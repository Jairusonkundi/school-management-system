<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../helpers/notify_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_role('admin');

$pageTitle = 'User Management';
$message = '';
$error = '';

function valid_manual_password(string $password): bool
{
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) === 1 && preg_match('/\d/', $password) === 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'add';

    try {
        if ($action === 'deactivate') {
            $stmt = $pdo->prepare('UPDATE users SET is_active = 0 WHERE user_id = ?');
            $stmt->execute([(int)$_POST['user_id']]);
            $message = 'User deactivated.';
        } elseif ($action === 'reset') {
            $mode = $_POST['reset_password_mode'] ?? 'auto';
            $userStmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
            $userStmt->execute([(int)$_POST['user_id']]);
            $user = $userStmt->fetch();
            if (!$user) {
                throw new RuntimeException('User not found.');
            }

            if ($mode === 'manual') {
                $password = (string)($_POST['reset_password'] ?? '');
                $confirm = (string)($_POST['reset_password_confirm'] ?? '');
                if ($password !== $confirm || !valid_manual_password($password)) {
                    throw new RuntimeException('Manual password must match confirmation, be at least 8 characters, and include a letter and a number.');
                }
                $emailMessage = 'Your SKA-SMS password has been reset by the Administrator. Contact the school office if you do not know the new password.';
            } else {
                $password = bin2hex(random_bytes(4));
                $emailMessage = "Your temporary password is {$password}";
            }

            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
            $stmt->execute([password_hash($password, PASSWORD_BCRYPT), (int)$user['user_id']]);
            send_notification($pdo, (int)$user['user_id'], $user['email'], $user['full_name'], 'Password reset', $emailMessage, 'Announcement');
            $message = 'Password reset.';
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare('UPDATE users SET full_name = ?, role = ?, is_active = ? WHERE user_id = ?');
            $stmt->execute([trim((string)$_POST['full_name']), $_POST['role'], (int)($_POST['is_active'] ?? 0), (int)$_POST['user_id']]);
            $message = 'User updated.';
        } else {
            $mode = $_POST['password_mode'] ?? 'auto';
            if ($mode === 'manual') {
                $password = (string)($_POST['password'] ?? '');
                $confirm = (string)($_POST['password_confirm'] ?? '');
                if ($password !== $confirm || !valid_manual_password($password)) {
                    throw new RuntimeException('Manual password must match confirmation, be at least 8 characters, and include a letter and a number.');
                }
                $emailMessage = 'Your SKA-SMS account has been created by the Administrator. Contact the school office if you do not know your password.';
            } else {
                $password = bin2hex(random_bytes(4));
                $emailMessage = "Your temporary password is {$password}";
            }

            $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                trim((string)$_POST['full_name']),
                strtolower(trim((string)$_POST['email'])),
                password_hash($password, PASSWORD_BCRYPT),
                $_POST['role'],
            ]);
            $id = (int)$pdo->lastInsertId();
            send_notification($pdo, $id, trim((string)$_POST['email']), trim((string)$_POST['full_name']), 'SKA account', $emailMessage, 'Announcement');
            $message = 'User created.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$users = $pdo->query('SELECT * FROM users ORDER BY role, full_name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<form method="post" class="card mb-3">
    <div class="card-body row g-3">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="add">
        <div class="col-md-3"><input class="form-control" name="full_name" placeholder="Full name" required></div>
        <div class="col-md-3"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
        <div class="col-md-2"><select class="form-select" name="role"><option>admin</option><option>teacher</option><option>parent</option><option>student</option></select></div>
        <div class="col-12">
            <label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="password_mode" value="auto" checked> Auto-generate password</label>
            <label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="password_mode" value="manual"> Set password manually</label>
        </div>
        <div class="col-12 row g-2 d-none" data-manual-password-fields>
            <div class="col-md-4"><input id="new-user-password" class="form-control" type="password" name="password" placeholder="Password"></div>
            <div class="col-md-4"><input class="form-control" type="password" name="password_confirm" placeholder="Confirm password"></div>
            <div class="col-md-2"><button class="btn btn-outline-secondary" type="button" data-password-toggle="#new-user-password">Show</button></div>
            <div class="col-12 small text-muted">Manual passwords must be at least 8 characters and include a letter and a number.</div>
        </div>
        <div class="col-md-2"><button class="btn btn-primary">Add User</button></div>
    </div>
</form>

<table class="table">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td colspan="5">
                <form method="post" class="row g-2 align-items-center">
                    <?= csrf_input() ?>
                    <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                    <div class="col-md-2"><input class="form-control" name="full_name" value="<?= h($u['full_name']) ?>"></div>
                    <div class="col-md-2"><?= h($u['email']) ?></div>
                    <div class="col-md-2"><select class="form-select" name="role"><?php foreach (['admin','teacher','parent','student'] as $r): ?><option<?= selected_attr($u['role'], $r) ?>><?= h($r) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-1"><select class="form-select" name="is_active"><option value="1"<?= selected_attr($u['is_active'], 1) ?>>Active</option><option value="0"<?= selected_attr($u['is_active'], 0) ?>>Inactive</option></select></div>
                    <div class="col-md-5">
                        <button class="btn btn-sm btn-primary" name="action" value="edit">Save</button>
                        <button class="btn btn-sm btn-danger" name="action" value="deactivate">Deactivate</button>
                        <div class="mt-2">
                            <label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="reset_password_mode" value="auto" checked> Auto reset</label>
                            <label class="form-check form-check-inline"><input class="form-check-input" type="radio" name="reset_password_mode" value="manual"> Manual reset</label>
                            <button class="btn btn-sm btn-warning" name="action" value="reset">Reset Password</button>
                        </div>
                        <div class="row g-2 mt-1 d-none" data-manual-password-fields>
                            <div class="col-md-5"><input id="reset-password-<?= (int)$u['user_id'] ?>" class="form-control form-control-sm" type="password" name="reset_password" placeholder="New password"></div>
                            <div class="col-md-5"><input class="form-control form-control-sm" type="password" name="reset_password_confirm" placeholder="Confirm"></div>
                            <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary" type="button" data-password-toggle="#reset-password-<?= (int)$u['user_id'] ?>">Show</button></div>
                        </div>
                    </div>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

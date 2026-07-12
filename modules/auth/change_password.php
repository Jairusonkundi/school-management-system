<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher','parent','student']);
$pageTitle='Change Password';$message=$error=''; if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$new=(string)$_POST['new_password']; if(strlen($new)<6){$error='Password must be at least 6 characters.';} else {$stmt=$pdo->prepare('UPDATE users SET password_hash=? WHERE user_id=?');$stmt->execute([password_hash($new,PASSWORD_BCRYPT),get_current_user_id()]);$message='Password changed.';}} require __DIR__ . '/../../includes/header.php';
?>
<?php if($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
<form method="post" class="card"><div class="card-body"><?= csrf_input() ?><label class="form-label">New Password</label><input class="form-control mb-3" type="password" name="new_password" required><button class="btn btn-primary">Change Password</button></div></form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

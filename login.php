<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/helpers/auth_helper.php';

$error = '';
if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lockedUntil = (int)($_SESSION['locked_until'] ?? 0);
    if ($lockedUntil > time()) {
        $error = 'Too many failed logins. Try again after ' . date('H:i', $lockedUntil) . '.';
    } else {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_attempts'], $_SESSION['locked_until']);
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            redirect('index.php');
        }
        $_SESSION['login_attempts'] = (int)($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['locked_until'] = time() + 900;
            $error = 'Too many failed logins. Account login is locked for 15 minutes.';
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SKA-SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-shell">
    <section class="auth-welcome">
        <div>
            <div class="brand-mark mb-4">SKA</div>
            <h1 class="display-6 fw-bold">Sunshine Kaseveni Academy</h1>
            <p class="lead">CBC school management from PP1 through Grade 9.</p>
        </div>
        <svg viewBox="0 0 280 220" role="img" aria-label="School illustration">
            <rect x="42" y="86" width="196" height="96" rx="12" fill="#E7F5EC"/>
            <path d="M30 90 140 28l110 62" fill="none" stroke="#F5A623" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="78" y="118" width="36" height="64" rx="4" fill="#1F7A4D"/>
            <rect x="134" y="116" width="28" height="28" rx="4" fill="#FF6B4A"/>
            <rect x="178" y="116" width="28" height="28" rx="4" fill="#FF6B4A"/>
            <path d="M54 182h172" stroke="#F5A623" stroke-width="10" stroke-linecap="round"/>
        </svg>
    </section>
    <section class="auth-form-panel">
        <h2 class="h3 fw-bold mb-1">Welcome back</h2>
        <p class="text-muted mb-4">Log in to continue to SKA-SMS.</p>
        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="auth-input">
                    <span class="input-icon">@</span>
                    <input class="form-control" type="email" name="email" autocomplete="email" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="auth-input">
                    <span class="input-icon">●</span>
                    <input id="login-password" class="form-control pe-5" type="password" name="password" autocomplete="current-password" required>
                    <button class="password-toggle" type="button" data-password-toggle="#login-password">Show</button>
                </div>
            </div>
            <?php if ($error !== ''): ?>
                <div class="alert auth-alert py-2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <button class="btn auth-primary w-100 py-2" type="submit">Log In</button>
            <div class="text-center mt-3">
                <a class="auth-link" href="modules/auth/forgot_password.php">Forgot password?</a>
            </div>
        </form>
    </section>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

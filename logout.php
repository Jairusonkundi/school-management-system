<?php
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="2;url=login.php">
    <title>Logged Out - SKA-SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-page">
<div class="logout-card p-4 text-center">
    <div class="brand-mark mx-auto mb-3">SKA</div>
    <h1 class="h4">You have been logged out</h1>
    <p class="text-muted">Sunshine Kaseveni Academy School Management System</p>
    <a class="btn auth-primary px-4" href="login.php">Log In Again</a>
</div>
</body>
</html>

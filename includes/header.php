<?php
@session_start();
if (empty($_SESSION['user_id'])) {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $parts = explode('/', trim($script, '/'));
    $base = isset($parts[0]) && $parts[0] !== '' ? '/' . $parts[0] : '';
    header('Location: ' . $base . '/login.php');
    exit;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../helpers/view_helper.php';
$pageTitle = $pageTitle ?? 'Sunshine Kaseveni Academy';
$basePath = app_base_path();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> - SKA-SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= h($basePath) ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top navbar-dark">
    <div class="container-fluid">
        <button class="sidebar-toggle d-md-none me-2" type="button" data-sidebar-toggle aria-label="Toggle navigation">☰</button>
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= h($basePath) ?>/index.php"><span class="brand-mark">SKA</span><span>Sunshine Kaseveni Academy</span></a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <?php $role = $_SESSION['role'] ?? ''; ?>
            <span class="d-none d-sm-inline"><?= h($_SESSION['full_name'] ?? '') ?></span>
            <span class="badge role-badge-<?= h($role) ?>"><?= h(ucfirst($role)) ?></span>
            <a class="btn btn-sm btn-outline-light" href="<?= h($basePath) ?>/logout.php">Logout</a>
        </div>
    </div>
</nav>
<?php require __DIR__ . '/sidebar.php'; ?>
<main class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4"><?= h($pageTitle) ?></h1>

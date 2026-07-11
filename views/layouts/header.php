<?php
use App\Core\Auth;
$user = Auth::user();
$flash = Auth::consumeFlash();
$route = $_GET['route'] ?? 'login';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sunshine Kaseveni Academy SMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php if ($user): ?>
<div class="app-container" style="display: flex; height: 100vh; width: 100vw; overflow: hidden; font-family: 'Inter', sans-serif; background-color: #f8fafc;">
    <aside class="sidebar" id="sidebar" style="width: 260px; min-width: 260px; background-color: #1e293b; color: #f8fafc; display: flex; flex-direction: column; height: 100%;">
        <div class="brand-section" style="padding: 20px; background-color: #0f172a; font-weight: 700; font-size: 1.1rem; border-bottom: 1px solid #334155;">
            SKA Sunshine Academy
        </div>
        <div class="search-wrapper" role="search" style="padding: 15px 20px;">
            <input type="text" placeholder="Search modules..." id="moduleSearch" style="width: 100%; padding: 8px 12px; background-color: #334155; border: none; border-radius: 6px; color: #fff; font-size: 0.875rem;">
        </div>
        <nav class="nav-menu" id="sidebarNav" style="flex-grow: 1; display: flex; flex-direction: column; gap: 4px; padding: 10px 15px; overflow-y: auto;">
            <a href="index.php?route=dashboard" class="nav-link <?= $route === 'dashboard' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px; font-weight: 500;"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
            <?php if (Auth::hasRole(['admin'])): ?>
            <a href="index.php?route=students/create" class="nav-link <?= $route === 'students/create' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-person-plus"></i><span>Admissions</span></a>
            <a href="index.php?route=users" class="nav-link <?= $route === 'users' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-people"></i><span>Users</span></a>
            <a href="index.php?route=teacher-assignments" class="nav-link <?= $route === 'teacher-assignments' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-person-workspace"></i><span>Teacher Assignments</span></a>
            <a href="index.php?route=finance/invoices" class="nav-link <?= $route === 'finance/invoices' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-receipt"></i><span>Invoices</span></a>
            <a href="index.php?route=reports" class="nav-link <?= $route === 'reports' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-bar-chart"></i><span>Reports</span></a>
            <?php endif; ?>
            <?php if (Auth::hasRole(['admin','teacher'])): ?>
            <a href="index.php?route=attendance" class="nav-link <?= $route === 'attendance' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
            <a href="index.php?route=academics/grades" class="nav-link <?= $route === 'academics/grades' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-journal-check"></i><span>Marks</span></a>
            <?php endif; ?>
            <?php if (Auth::hasRole(['admin'])): ?>
            <a href="index.php?route=finance/payments" class="nav-link <?= $route === 'finance/payments' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-credit-card"></i><span>Payments</span></a>
            <?php endif; ?>
            <?php if (Auth::hasRole(['parent','student'])): ?>
            <a href="index.php?route=portal" class="nav-link <?= $route === 'portal' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px;"><i class="bi bi-folder2-open"></i><span>My Records</span></a>
            <?php endif; ?>
        </nav>
    </aside>

    <div class="main-workspace" style="flex-grow: 1; display: flex; flex-direction: column; height: 100%; overflow: hidden;">
        <header class="top-navbar" style="height: 60px; background-color: #0087b7; color: #ffffff; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
                <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation" style="background: none; border: none; color: white; font-size: 1.25rem; cursor: pointer; padding: 0;"><i class="bi bi-list"></i></button>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <span class="notifications" aria-label="Notifications" style="cursor: pointer; position: relative;"><i class="bi bi-bell"></i><span class="notification-badge" style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px;">3</span></span>
                <span class="user-profile" style="font-size: 0.9rem; font-weight: 500;"><?= e($user['name']) ?> <?= e(ucfirst($user['role'])) ?></span>
                <form method="post" action="index.php?route=logout" class="header-logout">
                    <?= csrf_field() ?>
                    <button type="submit" title="Logout" style="background-color: #ef4444; color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Logout</button>
                </form>
            </div>
        </header>

        <main class="content-body" style="flex-grow: 1; overflow-y: auto; padding: 30px;">
            <div class="card-workspace" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 24px; border: 1px solid #e2e8f0;">
                <?php foreach ($flash as $type => $message): ?>
                    <div class="alert <?= $type === 'success' ? 'success' : '' ?>"><?= e($message) ?></div>
                <?php endforeach; ?>
<?php else: ?>
<div class="login-wrap">
<?php endif; ?>

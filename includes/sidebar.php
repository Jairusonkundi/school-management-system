<?php
$role = $_SESSION['role'] ?? '';
$basePath = app_base_path();
$icon = '<svg class="nav-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h9A1.5 1.5 0 0 1 14 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 12.5v-9Zm2 .5v2h8V4H4Zm0 4v4h8V8H4Z"/></svg>';
$links = [
    'admin' => [
        'General' => [['Dashboard', $basePath . '/dashboards/admin.php']],
        'Academic' => [['Students', $basePath . '/modules/students/index.php'], ['Academic', $basePath . '/modules/academic/attendance.php'], ['Reports', $basePath . '/modules/finance/reports.php']],
        'Operations' => [['Finance', $basePath . '/modules/finance/invoices.php'], ['Notifications', $basePath . '/modules/notifications/log.php'], ['User Management', $basePath . '/modules/users/index.php']],
    ],
    'teacher' => [
        'General' => [['Dashboard', $basePath . '/dashboards/teacher.php']],
        'Academic' => [['Attendance', $basePath . '/modules/academic/attendance.php'], ['Mark Entry', $basePath . '/modules/academic/marks.php'], ['Class Reports', $basePath . '/modules/academic/marksheet.php']],
    ],
    'parent' => [
        'General' => [['Dashboard', $basePath . '/dashboards/parent.php']],
        'Learner' => [["My Child's Attendance", $basePath . '/modules/academic/attendance_report.php'], ['Results', $basePath . '/modules/academic/reportcard.php'], ['Fee Statement', $basePath . '/modules/finance/reports.php'], ['Notifications', $basePath . '/modules/notifications/log.php']],
    ],
    'student' => [
        'General' => [['Dashboard', $basePath . '/dashboards/student.php']],
        'Learner' => [['My Results', $basePath . '/modules/academic/reportcard.php'], ['My Attendance', $basePath . '/modules/academic/attendance_report.php'], ['Announcements', $basePath . '/modules/notifications/log.php']],
    ],
];
$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
?>
<aside class="sidebar">
    <div class="sidebar-title">SKA-SMS</div>
    <nav class="nav flex-column">
        <?php foreach (($links[$role] ?? []) as $group => $groupLinks): ?>
            <div class="sidebar-section"><?= h($group) ?></div>
            <?php foreach ($groupLinks as $link): ?>
                <a class="nav-link<?= $currentPath === $link[1] ? ' active' : '' ?>" href="<?= h($link[1]) ?>"><?= $icon ?><span><?= h($link[0]) ?></span></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
</aside>

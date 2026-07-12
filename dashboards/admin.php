<?php
require_once __DIR__ . '/../helpers/view_helper.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');
$pageTitle = 'Admin Dashboard';
$term = current_term();
$year = current_academic_year();
$totalStudents = (int)$pdo->query('SELECT COUNT(*) FROM students WHERE is_active = 1')->fetchColumn();
$present = (int)$pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = 'Present'")->fetchColumn();
$attendanceRate = $totalStudents > 0 ? round(($present / $totalStudents) * 100, 1) : 0;
$stmt = $pdo->prepare('SELECT COALESCE(SUM(amount_paid),0) FROM fee_invoices WHERE term = ? AND academic_year = ?');
$stmt->execute([$term, $year]);
$collection = (float)$stmt->fetchColumn();
$pending = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE status IN ('Failed','Pending')")->fetchColumn();
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-3 mb-4">
    <?php foreach ([['Total Students', $totalStudents], ["Today's Attendance Rate", $attendanceRate . '%'], ['Term Fee Collection', money_fmt($collection)], ['Pending Notifications', $pending]] as $card): ?>
        <div class="col-md-3"><div class="card metric-card"><div class="card-body"><div class="text-muted"><?= h($card[0]) ?></div><div class="number"><?= h($card[1]) ?></div></div></div></div>
    <?php endforeach; ?>
</div>
<div class="row g-3">
    <?php foreach ([['Register Student','../modules/students/register.php'],['Attendance','../modules/academic/attendance.php'],['Subjects','../modules/academic/subjects.php'],['Fee Structure','../modules/finance/fee_structure.php'],['Invoices','../modules/finance/invoices.php'],['Announcements','../modules/notifications/announce.php']] as $tile): ?>
        <div class="col-md-4"><a class="card action-tile text-decoration-none text-dark" href="<?= h($tile[1]) ?>"><div class="card-body fw-bold"><?= h($tile[0]) ?></div></a></div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

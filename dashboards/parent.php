<?php
require_once __DIR__ . '/../helpers/view_helper.php';
require_once __DIR__ . '/../config/db.php';
require_role('parent');
$pageTitle = 'Parent Dashboard';
$stmt = $pdo->prepare('SELECT s.*, c.class_name FROM students s JOIN classes c ON c.class_id = s.class_id WHERE s.guardian_id = ? AND s.is_active = 1 LIMIT 1');
$stmt->execute([get_current_user_id()]);
$student = $stmt->fetch();
$results = $fees = $notifications = [];
$attendanceRate = 0;
if ($student) {
    $stmt = $pdo->prepare("SELECT COUNT(*) total, SUM(status='Present') present_count FROM attendance WHERE student_id = ?");
    $stmt->execute([(int)$student['student_id']]);
    $att = $stmt->fetch();
    $attendanceRate = (int)$att['total'] > 0 ? round(((int)$att['present_count'] / (int)$att['total']) * 100, 1) : 0;
    $stmt = $pdo->prepare('SELECT er.*, sub.subject_name FROM exam_results er JOIN subjects sub ON sub.subject_id = er.subject_id WHERE er.student_id = ? ORDER BY er.created_at DESC LIMIT 10');
    $stmt->execute([(int)$student['student_id']]);
    $results = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(balance),0) FROM fee_invoices WHERE student_id = ?');
    $stmt->execute([(int)$student['student_id']]);
    $fees = $stmt->fetchColumn();
}
$stmt = $pdo->prepare('SELECT * FROM notifications WHERE recipient_id = ? ORDER BY sent_at DESC LIMIT 5');
$stmt->execute([get_current_user_id()]);
$notifications = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<?php if (!$student): ?><div class="alert alert-info">No linked student record found.</div><?php else: ?>
<div class="row g-3 mb-4"><div class="col-md-4"><div class="card metric-card"><div class="card-body"><div><?= h($student['full_name']) ?></div><div class="number"><?= h($attendanceRate) ?>%</div><small>Attendance</small></div></div></div><div class="col-md-4"><div class="card metric-card"><div class="card-body"><div class="number"><?= money_fmt($fees) ?></div><small>Current Fee Balance</small></div></div></div></div>
<h2 class="h5">Latest Results</h2><table class="table"><thead><tr><th>Subject</th><th>Marks</th><th>CBC Level</th></tr></thead><tbody><?php foreach ($results as $r): ?><tr><td><?= h($r['subject_name']) ?></td><td><?= h($r['marks']) ?></td><td><span class="badge <?= h(cbc_badge_class($r['grade'])) ?>"><?= h(cbc_grade_label($r['grade'])) ?></span></td></tr><?php endforeach; ?></tbody></table>
<?php endif; ?>
<h2 class="h5">Notifications</h2><ul class="list-group"><?php foreach ($notifications as $n): ?><li class="list-group-item"><?= h($n['subject']) ?> <span class="badge text-bg-secondary"><?= h($n['status']) ?></span></li><?php endforeach; ?></ul>
<?php require __DIR__ . '/../includes/footer.php'; ?>

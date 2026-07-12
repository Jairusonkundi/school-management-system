<?php
require_once __DIR__ . '/../helpers/view_helper.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');
$pageTitle = 'Student Dashboard';
$stmt = $pdo->prepare('SELECT s.*, c.class_name FROM students s JOIN classes c ON c.class_id = s.class_id WHERE s.user_id = ? AND s.is_active = 1 LIMIT 1');
$stmt->execute([get_current_user_id()]);
$student = $stmt->fetch();
$results = $announcements = [];
$attendance = ['total' => 0, 'present_count' => 0];
if ($student) {
    $stmt = $pdo->prepare("SELECT COUNT(*) total, SUM(status='Present') present_count FROM attendance WHERE student_id = ?");
    $stmt->execute([(int)$student['student_id']]);
    $attendance = $stmt->fetch();
    $stmt = $pdo->prepare('SELECT er.*, sub.subject_name FROM exam_results er JOIN subjects sub ON sub.subject_id = er.subject_id WHERE er.student_id = ? ORDER BY er.created_at DESC LIMIT 10');
    $stmt->execute([(int)$student['student_id']]);
    $results = $stmt->fetchAll();
}
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_id = ? OR type = 'Announcement' ORDER BY sent_at DESC LIMIT 10");
$stmt->execute([get_current_user_id()]);
$announcements = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<?php if (!$student): ?><div class="alert alert-info">No linked student record found.</div><?php else: ?>
<div class="card metric-card mb-4"><div class="card-body"><h2 class="h5"><?= h($student['full_name']) ?></h2><p><?= h($student['admission_no']) ?> - <?= h($student['class_name']) ?></p><div class="number"><?= (int)$attendance['present_count'] ?> / <?= (int)$attendance['total'] ?></div><small>Days present</small></div></div>
<h2 class="h5">Latest Results</h2><table class="table"><thead><tr><th>Subject</th><th>Marks</th><th>CBC Level</th></tr></thead><tbody><?php foreach ($results as $r): ?><tr><td><?= h($r['subject_name']) ?></td><td><?= h($r['marks']) ?></td><td><span class="badge <?= h(cbc_badge_class($r['grade'])) ?>"><?= h(cbc_grade_label($r['grade'])) ?></span></td></tr><?php endforeach; ?></tbody></table>
<?php endif; ?>
<h2 class="h5">Announcements</h2><ul class="list-group"><?php foreach ($announcements as $n): ?><li class="list-group-item"><strong><?= h($n['subject']) ?></strong><br><?= h($n['message']) ?></li><?php endforeach; ?></ul>
<?php require __DIR__ . '/../includes/footer.php'; ?>

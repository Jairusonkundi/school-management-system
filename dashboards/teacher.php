<?php
require_once __DIR__ . '/../helpers/view_helper.php';
require_once __DIR__ . '/../config/db.php';
require_role('teacher');
$pageTitle = 'Teacher Dashboard';
$classes = fetch_classes($pdo, null, get_current_user_id());
$stmt = $pdo->prepare('SELECT er.*, s.full_name, sub.subject_name FROM exam_results er JOIN students s ON s.student_id = er.student_id JOIN subjects sub ON sub.subject_id = er.subject_id WHERE er.entered_by = ? ORDER BY er.created_at DESC LIMIT 10');
$stmt->execute([get_current_user_id()]);
$recent = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="card mb-4"><div class="card-body">
<h2 class="h5">Assigned Classes</h2>
<div class="table-responsive"><table class="table"><thead><tr><th>Class</th><th>Education Level</th><th>Year</th><th></th></tr></thead><tbody>
<?php foreach ($classes as $class): ?><tr><td><?= h(class_label($class)) ?></td><td><?= h($class['level_name'] ?? '') ?></td><td><?= h($class['academic_year']) ?></td><td><a class="btn btn-sm btn-primary" href="../modules/academic/attendance.php?class_id=<?= (int)$class['class_id'] ?>">Take Attendance</a></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<div class="card"><div class="card-body"><h2 class="h5">Recent Marks</h2>
<ul class="list-group"><?php foreach ($recent as $row): ?><li class="list-group-item"><?= h($row['full_name']) ?> - <?= h($row['subject_name']) ?>: <?= h($row['marks']) ?> <span class="badge <?= h(cbc_badge_class($row['grade'])) ?>"><?= h(cbc_grade_label($row['grade'])) ?></span></li><?php endforeach; ?></ul>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

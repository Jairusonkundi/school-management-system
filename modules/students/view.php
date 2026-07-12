<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher','parent','student']);
$pageTitle = 'Student Profile';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT s.*, c.class_name, c.grade_name, c.stream_name, e.level_name, u.full_name AS guardian_name, u.email AS guardian_email FROM students s JOIN classes c ON c.class_id=s.class_id LEFT JOIN education_levels e ON e.level_id=c.level_id LEFT JOIN users u ON u.user_id=s.guardian_id WHERE s.student_id=?');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { http_response_code(404); exit('Student not found'); }
$stmt = $pdo->prepare('SELECT * FROM disciplinary_records WHERE student_id=? ORDER BY incident_date DESC');
$stmt->execute([$id]);
$discipline = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT sub.subject_name, er.term, er.academic_year, er.marks, er.grade FROM exam_results er JOIN subjects sub ON sub.subject_id=er.subject_id WHERE er.student_id=? ORDER BY er.created_at DESC LIMIT 20');
$stmt->execute([$id]);
$results = $stmt->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="card mb-3"><div class="card-body"><h2 class="h5"><?= h($student['full_name']) ?></h2><p><?= h($student['admission_no']) ?> | <?= h(class_label($student)) ?> | <?= h($student['level_name']) ?></p><p>Guardian: <?= h($student['guardian_name']) ?> (<?= h($student['guardian_email']) ?>)</p><p><?= nl2br(h($student['medical_notes'])) ?></p></div></div>
<h2 class="h5">Academic Summary</h2><table class="table"><thead><tr><th>Subject</th><th>Term</th><th>Year</th><th>Marks</th><th>CBC Level</th></tr></thead><tbody><?php foreach ($results as $r): ?><tr><td><?= h($r['subject_name']) ?></td><td><?= h($r['term']) ?></td><td><?= h($r['academic_year']) ?></td><td><?= h($r['marks']) ?></td><td><span class="badge <?= h(cbc_badge_class($r['grade'])) ?>"><?= h(cbc_grade_label($r['grade'])) ?></span></td></tr><?php endforeach; ?></tbody></table>
<h2 class="h5">Disciplinary History</h2><table class="table"><thead><tr><th>Date</th><th>Description</th><th>Action</th></tr></thead><tbody><?php foreach ($discipline as $d): ?><tr><td><?= h($d['incident_date']) ?></td><td><?= h($d['description']) ?></td><td><?= h($d['action_taken']) ?></td></tr><?php endforeach; ?></tbody></table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

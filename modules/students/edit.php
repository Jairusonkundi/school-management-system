<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../helpers/notify_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_role('admin');
$pageTitle = 'Edit Student';
$id = (int)($_GET['id'] ?? $_POST['student_id'] ?? 0);
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $oldStmt = $pdo->prepare('SELECT class_id FROM students WHERE student_id = ?');
    $oldStmt->execute([$id]);
    $oldClass = (int)$oldStmt->fetchColumn();
    $stmt = $pdo->prepare('UPDATE students SET full_name=?, date_of_birth=?, gender=?, nationality=?, grade_level=?, class_id=?, date_of_admission=?, medical_notes=? WHERE student_id=?');
    $stmt->execute([trim((string)$_POST['full_name']), $_POST['date_of_birth'] ?: null, $_POST['gender'] ?: null, trim((string)$_POST['nationality']), $_POST['grade_level'], (int)$_POST['class_id'], $_POST['date_of_admission'], trim((string)$_POST['medical_notes']), $id]);
    if ($oldClass !== (int)$_POST['class_id']) {
        $stmt = $pdo->prepare('INSERT INTO disciplinary_records (student_id, incident_date, description, action_taken, recorded_by) VALUES (?, CURDATE(), ?, ?, ?)');
        $stmt->execute([$id, 'Class transfer audit entry', 'Transferred from class ID ' . $oldClass . ' to class ID ' . (int)$_POST['class_id'], get_current_user_id()]);
    }
    $message = 'Student updated.';
}
$stmt = $pdo->prepare('SELECT * FROM students WHERE student_id=?');
$stmt->execute([$id]);
$student = $stmt->fetch();
$classes = fetch_classes($pdo);
require __DIR__ . '/../../includes/header.php';
?>
<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<form method="post" class="card"><div class="card-body row g-3"><?= csrf_input() ?><input type="hidden" name="student_id" value="<?= (int)$id ?>">
<div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control" name="full_name" value="<?= h($student['full_name']) ?>" required></div>
<div class="col-md-3"><label class="form-label">Date of Birth</label><input class="form-control" type="date" name="date_of_birth" value="<?= h($student['date_of_birth']) ?>"></div>
<div class="col-md-3"><label class="form-label">Gender</label><select class="form-select" name="gender"><option></option><option<?= selected_attr($student['gender'],'Male') ?>>Male</option><option<?= selected_attr($student['gender'],'Female') ?>>Female</option><option<?= selected_attr($student['gender'],'Other') ?>>Other</option></select></div>
<div class="col-md-3"><label class="form-label">Nationality</label><input class="form-control" name="nationality" value="<?= h($student['nationality']) ?>"></div>
<div class="col-md-3"><label class="form-label">Grade</label><select class="form-select" name="grade_level"><?php foreach (grade_levels() as $grade): ?><option<?= selected_attr($student['grade_level'],$grade) ?>><?= h($grade) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Class</label><select class="form-select" name="class_id"><?php foreach ($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"<?= selected_attr($student['class_id'],$c['class_id']) ?>><?= h(class_label($c)) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Admission Date</label><input class="form-control" type="date" name="date_of_admission" value="<?= h($student['date_of_admission']) ?>" required></div>
<div class="col-12"><label class="form-label">Medical Notes</label><textarea class="form-control" name="medical_notes"><?= h($student['medical_notes']) ?></textarea></div>
<div class="col-12"><button class="btn btn-primary">Update</button></div></div></form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

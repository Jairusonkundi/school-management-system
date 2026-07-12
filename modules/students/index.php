<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher']);
$pageTitle = 'Students';
$q = trim((string)($_GET['q'] ?? ''));
$gradeLevel = trim((string)($_GET['grade_level'] ?? ''));
$classId = (int)($_GET['class_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * 20;
$sql = 'SELECT s.*, c.class_name, c.grade_name, c.stream_name FROM students s JOIN classes c ON c.class_id = s.class_id WHERE 1=1';
$params = [];
if (get_current_role() === 'teacher') {
    $sql .= ' AND c.teacher_id = ?';
    $params[] = get_current_user_id();
}
if ($q !== '') {
    $sql .= ' AND (s.full_name LIKE ? OR s.admission_no LIKE ?)';
    $params[] = "%{$q}%";
    $params[] = "%{$q}%";
}
if ($gradeLevel !== '') {
    $sql .= ' AND s.grade_level = ?';
    $params[] = $gradeLevel;
}
if ($classId > 0) {
    $sql .= ' AND s.class_id = ?';
    $params[] = $classId;
}
$sql .= ' ORDER BY s.created_at DESC LIMIT 20 OFFSET ' . $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
$classes = fetch_classes($pdo);
require __DIR__ . '/../../includes/header.php';
?>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-4"><input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Name or admission no"></div>
    <div class="col-md-2"><select class="form-select" name="grade_level"><option value="">All grades</option><?php foreach (grade_levels() as $grade): ?><option<?= selected_attr($gradeLevel,$grade) ?>><?= h($grade) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><select class="form-select" name="class_id"><option value="0">All classes</option><?php foreach ($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"<?= selected_attr($classId,$c['class_id']) ?>><?= h(class_label($c)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><button class="btn btn-primary">Search</button> <?php if (get_current_role()==='admin'): ?><a class="btn btn-success" href="register.php">Register</a><?php endif; ?></div>
</form>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Adm No</th><th>Full Name</th><th>Class</th><th>Grade</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($students as $s): ?><tr><td><?= h($s['admission_no']) ?></td><td><?= h($s['full_name']) ?></td><td><?= h(class_label($s)) ?></td><td><?= h($s['grade_level']) ?></td><td><a href="view.php?id=<?= (int)$s['student_id'] ?>">View</a> | <a href="edit.php?id=<?= (int)$s['student_id'] ?>">Edit</a> | <a href="discipline.php?id=<?= (int)$s['student_id'] ?>">Discipline</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<div class="no-print"><a class="btn btn-sm btn-outline-secondary" href="?page=<?= max(1,$page-1) ?>&q=<?= urlencode($q) ?>">Previous</a> <a class="btn btn-sm btn-outline-secondary" href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>">Next</a></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

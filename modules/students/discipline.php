<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher']);
$pageTitle = 'Disciplinary Records';
$id = (int)($_GET['id'] ?? $_POST['student_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = $pdo->prepare('INSERT INTO disciplinary_records (student_id, incident_date, description, action_taken, recorded_by) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$id, $_POST['incident_date'], trim((string)$_POST['description']), trim((string)$_POST['action_taken']), get_current_user_id()]);
}
$stmt = $pdo->prepare('SELECT * FROM students WHERE student_id=?');
$stmt->execute([$id]);
$student = $stmt->fetch();
$stmt = $pdo->prepare('SELECT dr.*, u.full_name AS recorder FROM disciplinary_records dr LEFT JOIN users u ON u.user_id=dr.recorded_by WHERE dr.student_id=? ORDER BY dr.incident_date DESC');
$stmt->execute([$id]);
$records = $stmt->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<h2 class="h5"><?= h($student['full_name'] ?? '') ?></h2>
<form method="post" class="card mb-3"><div class="card-body row g-3"><?= csrf_input() ?><input type="hidden" name="student_id" value="<?= (int)$id ?>"><div class="col-md-3"><input class="form-control" type="date" name="incident_date" value="<?= date('Y-m-d') ?>" required></div><div class="col-md-4"><textarea class="form-control" name="description" placeholder="Description" required></textarea></div><div class="col-md-4"><textarea class="form-control" name="action_taken" placeholder="Action taken"></textarea></div><div class="col-md-1"><button class="btn btn-primary">Add</button></div></div></form>
<table class="table"><thead><tr><th>Date</th><th>Description</th><th>Action</th><th>Recorded By</th></tr></thead><tbody><?php foreach ($records as $r): ?><tr><td><?= h($r['incident_date']) ?></td><td><?= h($r['description']) ?></td><td><?= h($r['action_taken']) ?></td><td><?= h($r['recorder']) ?></td></tr><?php endforeach; ?></tbody></table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

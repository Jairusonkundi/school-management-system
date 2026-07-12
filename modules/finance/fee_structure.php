<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_role('admin');
$pageTitle='Fee Structure';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$stmt=$pdo->prepare('INSERT INTO fee_structures (class_id,term,academic_year,amount) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE amount=VALUES(amount)');$stmt->execute([(int)$_POST['class_id'],(int)$_POST['term'],trim((string)$_POST['academic_year']),(float)$_POST['amount']]);}
$classes=fetch_classes($pdo);$rows=$pdo->query('SELECT fs.*,c.class_name FROM fee_structures fs JOIN classes c ON c.class_id=fs.class_id ORDER BY fs.academic_year DESC, fs.term, c.class_name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" class="card mb-3"><div class="card-body row g-2"><?= csrf_input() ?><div class="col-md-3"><select class="form-select" name="class_id"><?php foreach($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"><?= h(class_label($c)) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><select class="form-select" name="term"><option>1</option><option>2</option><option>3</option></select></div><div class="col-md-2"><input class="form-control" name="academic_year" value="<?= h(current_academic_year()) ?>"></div><div class="col-md-3"><input class="form-control" type="number" min="1" step="0.01" name="amount" required></div><div class="col-md-2"><button class="btn btn-primary">Save</button></div></div></form>
<table class="table"><thead><tr><th>Class</th><th>Term</th><th>Year</th><th>Amount</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= h($r['class_name']) ?></td><td><?= h($r['term']) ?></td><td><?= h($r['academic_year']) ?></td><td><?= money_fmt($r['amount']) ?></td></tr><?php endforeach; ?></tbody></table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

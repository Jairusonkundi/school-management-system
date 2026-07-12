<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher','parent','student']);
$pageTitle = 'Attendance Report';
$classId = (int)($_GET['class_id'] ?? 0);
$q = trim((string)($_GET['q'] ?? ''));
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
$sql = "SELECT s.student_id, s.full_name, s.admission_no, COUNT(a.attendance_id) total_days, SUM(a.status='Present') present_days, SUM(a.status='Absent') absent_days, SUM(a.status='Late') late_days FROM students s LEFT JOIN attendance a ON a.student_id=s.student_id AND a.date BETWEEN ? AND ? WHERE 1=1";
$params = [$from, $to];
if ($classId > 0) { $sql .= ' AND s.class_id=?'; $params[] = $classId; }
if ($q !== '') { $sql .= ' AND (s.full_name LIKE ? OR s.admission_no LIKE ?)'; $params[]="%{$q}%"; $params[]="%{$q}%"; }
if (get_current_role()==='parent') { $sql .= ' AND s.guardian_id=?'; $params[] = get_current_user_id(); }
if (get_current_role()==='student') { $sql .= ' AND s.user_id=?'; $params[] = get_current_user_id(); }
$sql .= ' GROUP BY s.student_id, s.full_name, s.admission_no ORDER BY s.full_name';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
$classes = fetch_classes($pdo);
require __DIR__ . '/../../includes/header.php';
?>
<form class="row g-2 mb-3 no-print"><div class="col-md-3"><select class="form-select" name="class_id"><option value="0">All classes</option><?php foreach($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"<?= selected_attr($classId,$c['class_id']) ?>><?= h(class_label($c)) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Student"></div><div class="col-md-2"><input class="form-control" type="date" name="from" value="<?= h($from) ?>"></div><div class="col-md-2"><input class="form-control" type="date" name="to" value="<?= h($to) ?>"></div><div class="col-md-2"><button class="btn btn-primary">Filter</button> <button type="button" onclick="print()" class="btn btn-secondary">Print</button></div></form>
<table class="table"><thead><tr><th>Student</th><th>Total Days</th><th>Present</th><th>Absent</th><th>Late</th><th>Attendance %</th></tr></thead><tbody><?php foreach($rows as $r): $pct=(int)$r['total_days']>0?round(((int)$r['present_days']/(int)$r['total_days'])*100,1):0; ?><tr><td><?= h($r['admission_no'].' - '.$r['full_name']) ?></td><td><?= (int)$r['total_days'] ?></td><td><?= (int)$r['present_days'] ?></td><td><?= (int)$r['absent_days'] ?></td><td><?= (int)$r['late_days'] ?></td><td><?= h($pct) ?>%</td></tr><?php endforeach; ?></tbody></table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

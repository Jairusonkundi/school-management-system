<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../helpers/notify_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher']);
$pageTitle = 'Daily Attendance';
$role = get_current_role();
$classId = (int)($_GET['class_id'] ?? $_POST['class_id'] ?? 0);
$date = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');
$classes = $role === 'teacher' ? fetch_classes($pdo, null, get_current_user_id()) : fetch_classes($pdo);
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['statuses'])) {
    verify_csrf();
    foreach ($_POST['statuses'] as $studentId => $status) {
        $studentId = (int)$studentId;
        $status = in_array($status, ['Present','Absent','Late'], true) ? $status : 'Present';
        $check = $pdo->prepare("SELECT status FROM attendance WHERE student_id=? AND date=?");
        $check->execute([$studentId, $date]);
        $old = $check->fetchColumn();
        $stmt = $pdo->prepare('INSERT INTO attendance (student_id, class_id, date, status, recorded_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=VALUES(status), class_id=VALUES(class_id), recorded_by=VALUES(recorded_by)');
        $stmt->execute([$studentId, $classId, $date, $status, get_current_user_id()]);
        if ($status === 'Absent' && $old !== 'Absent') {
            $stmt = $pdo->prepare('SELECT s.full_name, u.user_id, u.email, u.full_name guardian_name FROM students s LEFT JOIN users u ON u.user_id=s.guardian_id WHERE s.student_id=?');
            $stmt->execute([$studentId]);
            $s = $stmt->fetch();
            if ($s && !empty($s['user_id'])) {
                $dup = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id=? AND type='Absence' AND message LIKE ?");
                $dup->execute([(int)$s['user_id'], '%' . $date . '%']);
                if ((int)$dup->fetchColumn() === 0) {
                    send_notification($pdo, (int)$s['user_id'], $s['email'], $s['guardian_name'], 'Absence alert', $s['full_name'] . ' was marked absent on ' . $date, 'Absence');
                }
            }
        }
    }
    $message = 'Attendance saved.';
}
$students = [];
if ($classId > 0) {
    $stmt = $pdo->prepare('SELECT s.*, a.status FROM students s LEFT JOIN attendance a ON a.student_id=s.student_id AND a.date=? WHERE s.class_id=? AND s.is_active=1 ORDER BY s.full_name');
    $stmt->execute([$date, $classId]);
    $students = $stmt->fetchAll();
}
require __DIR__ . '/../../includes/header.php';
?>
<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<form class="row g-2 mb-3" method="get"><div class="col-md-4"><select class="form-select" name="class_id" required><option value="">Select class</option><?php foreach ($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"<?= selected_attr($classId,$c['class_id']) ?>><?= h($c['class_name']) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><input class="form-control" type="date" name="date" value="<?= h($date) ?>"></div><div class="col-md-2"><button class="btn btn-primary">Load</button></div></form>
<?php if ($students): ?><form method="post"><?= csrf_input() ?><input type="hidden" name="class_id" value="<?= (int)$classId ?>"><input type="hidden" name="date" value="<?= h($date) ?>"><table class="table"><thead><tr><th>Student</th><th>Present</th><th>Absent</th><th>Late</th></tr></thead><tbody><?php foreach ($students as $s): $status=$s['status'] ?: 'Present'; ?><tr><td><?= h($s['admission_no'].' - '.$s['full_name']) ?></td><?php foreach (['Present','Absent','Late'] as $opt): ?><td><input type="radio" name="statuses[<?= (int)$s['student_id'] ?>]" value="<?= h($opt) ?>"<?= checked_attr($status,$opt) ?>></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table><button class="btn btn-success">Save Attendance</button></form><?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

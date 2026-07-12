<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../helpers/notify_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_role('admin');
$pageTitle = 'Register Student';
$message = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $gradeLevel = in_array($_POST['grade_level'] ?? '', grade_levels(), true) ? $_POST['grade_level'] : 'Grade 7';
        $year = date('Y');
        $stmt = $pdo->prepare('SELECT admission_no FROM students WHERE admission_no LIKE ? ORDER BY student_id DESC LIMIT 1');
        $stmt->execute(["SKA-{$year}-%"]);
        $last = (string)$stmt->fetchColumn();
        preg_match('/(\d+)$/', $last, $m);
        $admissionNo = 'SKA-' . $year . '-' . str_pad((string)(((int)($m[1] ?? 0)) + 1), 5, '0', STR_PAD_LEFT);
        $guardianId = null;
        $guardianEmail = strtolower(trim((string)($_POST['guardian_email'] ?? '')));
        if ($guardianEmail !== '') {
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$guardianEmail]);
            $guardianId = $stmt->fetchColumn();
            if (!$guardianId) {
                $temp = bin2hex(random_bytes(4));
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'parent')");
                $stmt->execute([trim((string)$_POST['guardian_name']), $guardianEmail, password_hash($temp, PASSWORD_BCRYPT)]);
                $guardianId = (int)$pdo->lastInsertId();
                send_notification($pdo, $guardianId, $guardianEmail, trim((string)$_POST['guardian_name']), 'SKA parent account', "Your temporary password is {$temp}", 'Announcement');
            }
        }
        $stmt = $pdo->prepare('INSERT INTO students (admission_no, full_name, date_of_birth, gender, nationality, grade_level, class_id, guardian_id, guardian_phone, emergency_contact_name, emergency_contact_phone, date_of_admission, medical_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$admissionNo, trim((string)$_POST['full_name']), $_POST['date_of_birth'] ?: null, $_POST['gender'] ?: null, trim((string)$_POST['nationality']), $gradeLevel, (int)$_POST['class_id'], $guardianId ?: null, trim((string)$_POST['guardian_phone']), trim((string)$_POST['emergency_contact_name']), trim((string)$_POST['emergency_contact_phone']), $_POST['date_of_admission'], trim((string)$_POST['medical_notes'])]);
        $message = "Student registered with admission number {$admissionNo}.";
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Student registration failed. Check the form and try again.';
    }
}
$classes = fetch_classes($pdo);
require __DIR__ . '/../../includes/header.php';
?>
<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
<form method="post" class="card"><div class="card-body row g-3"><?= csrf_input() ?>
<?php foreach ([['full_name','Full Name','text'],['date_of_birth','Date of Birth','date'],['nationality','Nationality','text'],['date_of_admission','Date of Admission','date'],['guardian_name','Guardian Name','text'],['guardian_phone','Guardian Phone','text'],['guardian_email','Guardian Email','email'],['emergency_contact_name','Emergency Contact Name','text'],['emergency_contact_phone','Emergency Contact Phone','text']] as $f): ?><div class="col-md-4"><label class="form-label"><?= h($f[1]) ?></label><input class="form-control" type="<?= h($f[2]) ?>" name="<?= h($f[0]) ?>" <?= in_array($f[0], ['full_name','date_of_admission'], true) ? 'required' : '' ?>></div><?php endforeach; ?>
<div class="col-md-4"><label class="form-label">Gender</label><select class="form-select" name="gender"><option></option><option>Male</option><option>Female</option><option>Other</option></select></div>
<div class="col-md-4"><label class="form-label">Grade</label><select class="form-select" name="grade_level" required><?php foreach (grade_levels() as $grade): ?><option><?= h($grade) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Class</label><select class="form-select" name="class_id" required><?php foreach ($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"><?= h(class_label($c)) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Medical Notes</label><textarea class="form-control" name="medical_notes"></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save Student</button></div></div></form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

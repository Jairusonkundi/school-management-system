<?php

$root = dirname(__DIR__);
$failures = [];

function check(bool $condition, string $message): void
{
    global $failures;
    if (!$condition) {
        $failures[] = $message;
    }
}

function source(string $relative): string
{
    global $root;
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return file_exists($path) ? (string)file_get_contents($path) : '';
}

$schema = source('database/schema.sql');
$auth = source('app/Core/Auth.php');
$public = source('public/index.php');
$student = source('app/Models/Student.php');
$attendance = source('app/Models/Attendance.php');
$academic = source('app/Models/Academic.php');
$finance = source('app/Models/Finance.php');
$user = source('app/Models/User.php');
$notifications = source('app/Services/NotificationService.php');
$header = source('views/layouts/header.php');

check(str_contains($schema, 'FOREIGN KEY'), 'Schema must define foreign keys.');
check(str_contains($schema, 'admission_sequences'), 'Schema must include admission sequence table.');
check(str_contains($schema, 'CHECK (marks >= 0 AND marks <= 100)'), 'Schema must constrain exam marks.');
check(str_contains($schema, 'CHECK (balance >= 0 AND balance <= amount_due)'), 'Schema must constrain invoice balances.');

check(str_contains($auth, 'session_regenerate_id(true)'), 'Login must regenerate session IDs.');
check(str_contains($auth, 'session.cookie_httponly'), 'Sessions must use HTTP-only cookies.');
check(str_contains($auth, 'hash_equals'), 'CSRF verification must use hash_equals.');
check(str_contains($public, 'X-Frame-Options'), 'Security headers must be sent.');
check(str_contains($header, 'method="post" action="index.php?route=logout"'), 'Logout must be a POST form.');

check(str_contains($student, 'FOR UPDATE'), 'Admission number generation must lock the sequence row.');
check(str_contains($student, 'canAccessStudent'), 'Student access helper must exist for ownership checks.');
check(str_contains($attendance, 'teacher_assignments'), 'Attendance queries must enforce teacher assignments.');
check(str_contains($academic, 'RANK() OVER'), 'Academic results must compute rank.');
check(str_contains($academic, 'AVG(er.marks) OVER'), 'Academic results must compute class average.');
check(str_contains($finance, 'Payment exceeds the outstanding balance'), 'Payments must reject overpayment.');
check(str_contains($finance, 'FOR UPDATE'), 'Payment recording must lock invoice rows.');
check(str_contains($notifications, 'PHPMailer'), 'Email notifications must use PHPMailer.');
check(str_contains($user, 'password_hash'), 'User passwords must be hashed.');
check(str_contains($user, 'deactivate'), 'User deactivation must be implemented.');

if ($failures) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "[FAIL] {$failure}\n");
    }
    exit(1);
}

echo "Basic requirement checks passed.\n";

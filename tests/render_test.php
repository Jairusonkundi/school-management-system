<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\Finance;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Academic;
use App\Models\Lookup;
use App\Models\User;
use App\Models\TeacherAssignment;

Auth::start();
Database::getConnection();
$adminUser = (new User())->findByEmail('admin@sunshine.local');
Auth::login($adminUser);

$views = [
    'admin/dashboard' => function() {
        return [
            'counts' => (new Student())->countsByClass(),
            'finance' => (new Finance())->summary(),
            'attendance' => (new Attendance())->todayStats(),
        ];
    },
    'finance/invoices' => function() {
        return [
            'lookups' => new Lookup(),
            'invoices' => (new Finance())->invoices(),
        ];
    },
    'finance/payments' => function() {
        $f = new Finance();
        return [
            'invoices' => $f->outstandingInvoices(),
            'allInvoices' => $f->invoices(),
        ];
    },
    'reports/index' => function() {
        return [
            'enrollment' => (new Student())->countsByClass(),
            'attendance' => (new Attendance())->summaryByClass(),
            'performance' => (new Academic())->performanceSummary(),
            'finance' => (new Finance())->collectionSummary(),
        ];
    },
    'students/create' => function() {
        return [
            'lookups' => new Lookup(),
            'students' => (new Student())->all(),
        ];
    },
    'users/index' => function() {
        return [
            'lookups' => new Lookup(),
            'users' => (new User())->all(),
            'roles' => Auth::roles(),
        ];
    },
    'teachers/assignments' => function() {
        return [
            'lookups' => new Lookup(),
            'assignments' => (new TeacherAssignment())->all(),
        ];
    },
    'teacher/dashboard' => function() { return []; },
    'parent/dashboard' => function() { return []; },
    'student/dashboard' => function() { return []; },
    'attendance/mark' => function() {
        $user = Auth::user();
        return [
            'students' => (new Student())->accessibleForUser($user),
            'classes' => (new Lookup())->classesForUser($user),
            'history' => (new Attendance())->history(null, null, null, $user),
        ];
    },
    'academics/grades' => function() {
        $user = Auth::user();
        return [
            'lookups' => new Lookup(),
            'classes' => (new Lookup())->classesForUser($user),
            'subjects' => (new Lookup())->subjectsForUser($user),
            'students' => (new Student())->accessibleForUser($user),
            'results' => (new Academic())->results(null, null, $user),
        ];
    },
    'portal/index' => function() {
        $user = Auth::user();
        return [
            'students' => (new Student())->accessibleForUser($user),
            'attendance' => (new Attendance())->history(null, null, null, $user),
            'results' => (new Academic())->results(null, null, $user),
            'invoices' => (new Finance())->statementForUser($user),
        ];
    },
];

$errors = [];
foreach ($views as $view => $dataFn) {
    try {
        $data = $dataFn();
        ob_start();
        extract($data);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
        $html = ob_get_clean();

        $checks = [
            'app-wrapper' => strpos($html, 'app-wrapper') !== false,
            'sidebar' => strpos($html, 'class="sidebar"') !== false,
            'sidebar-nav' => strpos($html, 'sidebar-nav') !== false,
            'sidebar-link' => strpos($html, 'sidebar-link') !== false,
            'top-header' => strpos($html, 'top-header') !== false,
            'sidebar-toggle' => strpos($html, 'sidebar-toggle') !== false,
            'content-body' => strpos($html, 'content-body') !== false,
            'main-footer' => strpos($html, 'main-footer') !== false,
        ];

        $failed = [];
        foreach ($checks as $key => $val) {
            if (!$val) $failed[] = $key;
        }

        if ($failed) {
            echo "FAIL {$view} - missing: " . implode(', ', $failed) . "\n";
            $errors[] = $view;
        } else {
            echo "OK   {$view} (" . strlen($html) . " bytes)\n";
        }
    } catch (\Throwable $e) {
        echo "ERR  {$view}: " . $e->getMessage() . "\n";
        $errors[] = $view;
    }
}

// Test login page (no auth)
Auth::logout();
ob_start();
$error = '';
require __DIR__ . '/../views/layouts/header.php';
require __DIR__ . '/../views/auth/login.php';
require __DIR__ . '/../views/layouts/footer.php';
$html = ob_get_clean();

$loginChecks = [
    'auth' => strpos($html, 'class="auth"') !== false,
    'login-wrap' => strpos($html, 'login-wrap') !== false,
    'login-footer' => strpos($html, 'login-footer') !== false,
    'no-sidebar' => strpos($html, 'sidebar-nav') === false,
];

$failed = [];
foreach ($loginChecks as $key => $val) {
    if (!$val) $failed[] = $key;
}
if ($failed) {
    echo "FAIL auth/login - missing: " . implode(', ', $failed) . "\n";
    $errors[] = 'auth/login';
} else {
    echo "OK   auth/login (" . strlen($html) . " bytes)\n";
}

echo "\n" . (empty($errors) ? "ALL PASSED" : count($errors) . " FAILED") . "\n";

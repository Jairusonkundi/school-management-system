<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/functions.php';

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use App\Controllers\AcademicController;
use App\Controllers\FinanceController;
use App\Controllers\AttendanceController;
use App\Controllers\UserController;
use App\Controllers\PortalController;
use App\Controllers\ReportController;
use App\Controllers\TeacherAssignmentController;

$route = $_GET['route'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'login' => [AuthController::class, $method === 'POST' ? 'login' : 'showLogin'],
    'logout' => [AuthController::class, 'logout'],
    'dashboard' => [DashboardController::class, 'index'],
    'students/create' => [StudentController::class, $method === 'POST' ? 'store' : 'create'],
    'academics/grades' => [AcademicController::class, $method === 'POST' ? 'storeGrade' : 'grades'],
    'finance/invoices' => [FinanceController::class, $method === 'POST' ? 'storeInvoice' : 'invoices'],
    'finance/payments' => [FinanceController::class, $method === 'POST' ? 'storePayment' : 'payments'],
    'attendance' => [AttendanceController::class, $method === 'POST' ? 'store' : 'mark'],
    'users' => [UserController::class, $method === 'POST' ? 'store' : 'index'],
    'users/deactivate' => [UserController::class, 'deactivate'],
    'teacher-assignments' => [TeacherAssignmentController::class, $method === 'POST' ? 'store' : 'index'],
    'teacher-assignments/delete' => [TeacherAssignmentController::class, 'delete'],
    'portal' => [PortalController::class, 'index'],
    'reports' => [ReportController::class, 'index'],
];

if (!isset($routes[$route])) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

[$class, $action] = $routes[$route];
(new $class())->$action();

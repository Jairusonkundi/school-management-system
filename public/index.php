<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/functions.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use App\Controllers\AcademicController;
use App\Controllers\FinanceController;
use App\Controllers\AttendanceController;

$route = $_GET['route'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'login' => [AuthController::class, $method === 'POST' ? 'login' : 'showLogin'],
    'logout' => [AuthController::class, 'logout'],
    'dashboard' => [DashboardController::class, 'index'],
    'students/create' => [StudentController::class, $method === 'POST' ? 'store' : 'create'],
    'academics/grades' => [AcademicController::class, $method === 'POST' ? 'storeGrade' : 'grades'],
    'academics/cbc' => [AcademicController::class, $method === 'POST' ? 'storeCbc' : 'cbc'],
    'finance/payments' => [FinanceController::class, $method === 'POST' ? 'storePayment' : 'payments'],
    'attendance' => [AttendanceController::class, $method === 'POST' ? 'store' : 'mark'],
];

if (!isset($routes[$route])) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

[$class, $action] = $routes[$route];
(new $class())->$action();

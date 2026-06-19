<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Attendance;
use App\Models\Finance;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin','teacher','student','parent']);
        $role = Auth::user()['role'];
        $data = ['counts' => (new Student())->countsBySection(), 'finance' => (new Finance())->summary(), 'attendance' => (new Attendance())->todayStats()];
        $this->view($role . '/dashboard', $data);
    }
}

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
        $data = [];
        if ($role === 'admin') {
            $data = [
                'counts' => (new Student())->countsByClass(),
                'finance' => (new Finance())->summary(),
                'attendance' => (new Attendance())->todayStats(),
            ];
        }
        $view = in_array($role, ['parent', 'student'], true) ? $role . '/dashboard' : $role . '/dashboard';
        $this->view($view, $data);
    }
}

<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Academic;
use App\Models\Attendance;
use App\Models\Finance;
use App\Models\Student;

class PortalController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['parent','student']);
        $user = Auth::user();
        $this->view('portal/index', [
            'students' => (new Student())->accessibleForUser($user),
            'attendance' => (new Attendance())->history(null, null, null, $user),
            'results' => (new Academic())->results(null, null, $user),
            'invoices' => (new Finance())->statementForUser($user),
        ]);
    }
}

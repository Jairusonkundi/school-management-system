<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Academic;
use App\Models\Attendance;
use App\Models\Finance;
use App\Models\Student;

class ReportController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $this->view('reports/index', [
            'enrollment' => (new Student())->countsByClass(),
            'attendance' => (new Attendance())->summaryByClass(),
            'performance' => (new Academic())->performanceSummary(),
            'finance' => (new Finance())->collectionSummary(),
        ]);
    }
}

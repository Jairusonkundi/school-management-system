<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Attendance;
use App\Models\Lookup;

class AttendanceController extends Controller
{
    public function mark(): void { Auth::requireRole(['admin','teacher']); $this->view('attendance/mark', ['lookups' => new Lookup()]); }
    public function store(): void
    {
        Auth::requireRole(['admin','teacher']);
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $data['marked_by'] = Auth::user()['id'];
        (new Attendance())->mark($data);
        $this->redirect('attendance');
    }
}

<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Attendance;
use App\Models\Lookup;
use App\Models\Student;
use App\Services\NotificationService;
use Throwable;

class AttendanceController extends Controller
{
    public function mark(): void
    {
        Auth::requireRole(['admin','teacher']);
        $user = Auth::user();
        $history = [];
        try {
            $history = (new Attendance())->history((int)($_GET['class_id'] ?? 0) ?: null, $_GET['from'] ?? null, $_GET['to'] ?? null, $user);
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->view('attendance/mark', [
            'lookups' => new Lookup(),
            'classes' => (new Lookup())->classesForUser($user),
            'students' => (new Student())->accessibleForUser($user),
            'history' => $history,
        ]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin','teacher']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            $student = (new Attendance())->mark($_POST, Auth::user());
            if (($_POST['status'] ?? '') === 'absent' && empty($_POST['note']) && !empty($student['guardian_user_id']) && !empty($student['guardian_email'])) {
                $message = $student['name'] . ' was marked absent on ' . $_POST['date'] . ' without an excuse on record.';
                (new NotificationService())->sendToUser((int)$student['guardian_user_id'], $student['guardian_email'], 'absence_alert', $message);
            }
            Auth::flash('success', 'Attendance saved.');
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('attendance');
    }
}

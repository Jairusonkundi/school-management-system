<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Lookup;
use App\Models\TeacherAssignment;
use Throwable;

class TeacherAssignmentController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $this->view('teachers/assignments', ['lookups' => new Lookup(), 'assignments' => (new TeacherAssignment())->all()]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            (new TeacherAssignment())->create($_POST);
            Auth::flash('success', 'Teacher assignment saved.');
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('teacher-assignments');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            (new TeacherAssignment())->delete((int)post('id'));
            Auth::flash('success', 'Teacher assignment removed.');
        } catch (Throwable $e) {
            Auth::flash('error', 'Unable to remove that teacher assignment.');
        }
        $this->redirect('teacher-assignments');
    }
}

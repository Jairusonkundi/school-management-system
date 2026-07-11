<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Lookup;
use App\Models\Student;
use Throwable;

class StudentController extends Controller
{
    public function create(): void
    {
        Auth::requireRole(['admin']);
        $this->view('students/create', ['lookups' => new Lookup(), 'students' => (new Student())->all()]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            $id = (new Student())->create($_POST);
            $student = (new Student())->find($id);
            Auth::flash('success', 'Student registered with admission number ' . ($student['admission_no'] ?? ''));
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('students/create');
    }
}

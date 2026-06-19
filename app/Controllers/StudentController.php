<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Lookup;
use App\Models\Student;

class StudentController extends Controller
{
    public function create(): void { Auth::requireRole(['admin']); $this->view('students/create', ['lookups' => new Lookup()]); }
    public function store(): void
    {
        Auth::requireRole(['admin']);
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        (new Student())->create($data);
        $this->redirect('students/create');
    }
}

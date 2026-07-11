<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Academic;
use App\Models\Lookup;
use App\Models\Student;
use Throwable;

class AcademicController extends Controller
{
    public function grades(): void
    {
        Auth::requireRole(['admin','teacher']);
        $user = Auth::user();
        $results = [];
        try {
            $results = (new Academic())->results((int)($_GET['class_id'] ?? 0) ?: null, $_GET['term'] ?? null, $user);
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->view('academics/grades', [
            'lookups' => new Lookup(),
            'classes' => (new Lookup())->classesForUser($user),
            'subjects' => (new Lookup())->subjectsForUser($user),
            'students' => (new Student())->accessibleForUser($user),
            'results' => $results,
        ]);
    }

    public function storeGrade(): void
    {
        Auth::requireRole(['admin','teacher']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            (new Academic())->recordResult($_POST, Auth::user());
            Auth::flash('success', 'Marks saved and grade recalculated.');
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('academics/grades');
    }
}

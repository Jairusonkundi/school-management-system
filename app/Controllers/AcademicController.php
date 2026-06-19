<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Academic;
use App\Models\Lookup;

class AcademicController extends Controller
{
    public function grades(): void { Auth::requireRole(['admin','teacher']); $this->view('academics/grades', ['lookups' => new Lookup()]); }
    public function storeGrade(): void { Auth::requireRole(['admin','teacher']); (new Academic())->recordGrade(filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS)); $this->redirect('academics/grades'); }
    public function cbc(): void { Auth::requireRole(['admin','teacher']); $this->view('academics/cbc', ['lookups' => new Lookup()]); }
    public function storeCbc(): void { Auth::requireRole(['admin','teacher']); (new Academic())->recordCbc(filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS)); $this->redirect('academics/cbc'); }
}

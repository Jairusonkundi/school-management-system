<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Finance;

class FinanceController extends Controller
{
    public function payments(): void { Auth::requireRole(['admin']); $this->view('finance/payments', ['accounts' => (new Finance())->feeAccounts()]); }
    public function storePayment(): void
    {
        Auth::requireRole(['admin']);
        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $data['received_by'] = Auth::user()['id'];
        (new Finance())->recordPayment($data);
        $this->redirect('finance/payments');
    }
}

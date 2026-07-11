<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Finance;
use App\Models\Lookup;
use App\Services\NotificationService;
use Throwable;

class FinanceController extends Controller
{
    public function invoices(): void
    {
        Auth::requireRole(['admin']);
        $this->view('finance/invoices', ['lookups' => new Lookup(), 'invoices' => (new Finance())->invoices()]);
    }

    public function storeInvoice(): void
    {
        Auth::requireRole(['admin']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            (new Finance())->createInvoice($_POST, Auth::user());
            Auth::flash('success', 'Invoice saved.');
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('finance/invoices');
    }

    public function payments(): void
    {
        Auth::requireRole(['admin']);
        $finance = new Finance();
        $this->view('finance/payments', ['invoices' => $finance->outstandingInvoices(), 'allInvoices' => $finance->invoices()]);
    }

    public function storePayment(): void
    {
        Auth::requireRole(['admin']);
        Auth::verifyCsrf(post('csrf_token'));
        try {
            $receipt = (new Finance())->recordPayment($_POST, Auth::user());
            if (!empty($receipt['guardian_user_id']) && !empty($receipt['guardian_email'])) {
                $message = "Receipt #" . $receipt['payment_id'] . "\nStudent: " . $receipt['student_name'] . "\nAdmission No: " . $receipt['admission_no'] . "\nPaid: " . money($receipt['paid_amount']) . "\nBalance: " . money($receipt['new_balance']);
                (new NotificationService())->sendToUser((int)$receipt['guardian_user_id'], $receipt['guardian_email'], 'payment_receipt', $message);
            }
            Auth::flash('success', 'Payment recorded and receipt email attempted.');
        } catch (Throwable $e) {
            Auth::flash('error', $e->getMessage());
        }
        $this->redirect('finance/payments');
    }
}

<?php
namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class Finance extends Model
{
    public function createInvoice(array $data, array $user): void
    {
        $studentId = (int)($data['student_id'] ?? 0);
        $term = trim($data['term'] ?? '');
        $amount = (float)($data['amount_due'] ?? 0);
        if (!$this->activeStudentExists($studentId)) {
            throw new InvalidArgumentException('Select a valid active student.');
        }
        if ($term === '') {
            throw new InvalidArgumentException('Term is required.');
        }
        if ($amount <= 0) {
            throw new InvalidArgumentException('Invoice amount must be greater than zero.');
        }

        $this->db->beginTransaction();
        try {
            $existing = $this->db->prepare('SELECT * FROM fee_invoices WHERE student_id = ? AND term = ? FOR UPDATE');
            $existing->execute([$studentId, $term]);
            $invoice = $existing->fetch();
            if ($invoice) {
                $paid = (float)$invoice['amount_due'] - (float)$invoice['balance'];
                if ($amount < $paid) {
                    throw new InvalidArgumentException('Invoice amount cannot be less than payments already recorded.');
                }
                $stmt = $this->db->prepare('UPDATE fee_invoices SET amount_due = ?, balance = ? WHERE id = ?');
                $stmt->execute([$amount, $amount - $paid, (int)$invoice['id']]);
            } else {
                $stmt = $this->db->prepare('INSERT INTO fee_invoices (student_id, term, amount_due, balance, created_by) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$studentId, $term, $amount, $amount, $user['id']]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recordPayment(array $data, array $user): array
    {
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }
        if (!$this->validDate($data['date'] ?? '')) {
            throw new InvalidArgumentException('Enter a valid payment date.');
        }
        if (!in_array($data['method'] ?? '', ['cash', 'mpesa', 'bank', 'cheque'], true)) {
            throw new InvalidArgumentException('Select a valid payment method.');
        }
        if (trim($data['reference'] ?? '') === '') {
            throw new InvalidArgumentException('Payment reference is required.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT fi.*, s.name AS student_name, s.admission_no, guardian.email AS guardian_email, guardian.id AS guardian_user_id FROM fee_invoices fi JOIN students s ON s.id = fi.student_id LEFT JOIN users guardian ON guardian.id = s.guardian_user_id WHERE fi.id = ? FOR UPDATE');
            $stmt->execute([(int)$data['invoice_id']]);
            $invoice = $stmt->fetch();
            if (!$invoice) {
                throw new InvalidArgumentException('Invoice not found.');
            }
            if ($amount > (float)$invoice['balance']) {
                throw new InvalidArgumentException('Payment exceeds the outstanding balance.');
            }

            $insert = $this->db->prepare('INSERT INTO payments (invoice_id, amount, date, method, reference, recorded_by) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute([
                (int)$data['invoice_id'],
                $amount,
                $data['date'],
                $data['method'],
                trim($data['reference']),
                $user['id'],
            ]);
            $paymentId = (int)$this->db->lastInsertId();
            $update = $this->db->prepare('UPDATE fee_invoices SET balance = balance - ? WHERE id = ?');
            $update->execute([$amount, (int)$data['invoice_id']]);
            $this->db->commit();
            $invoice['payment_id'] = $paymentId;
            $invoice['paid_amount'] = $amount;
            $invoice['new_balance'] = (float)$invoice['balance'] - $amount;
            return $invoice;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function invoices(): array
    {
        return $this->db->query('SELECT fi.*, s.admission_no, s.name AS student_name FROM fee_invoices fi JOIN students s ON s.id = fi.student_id ORDER BY fi.created_at DESC')->fetchAll();
    }

    public function outstandingInvoices(): array
    {
        return $this->db->query('SELECT fi.*, s.admission_no, s.name AS student_name FROM fee_invoices fi JOIN students s ON s.id = fi.student_id WHERE fi.balance > 0 ORDER BY fi.created_at DESC')->fetchAll();
    }

    public function statementForUser(array $user): array
    {
        $sql = 'SELECT fi.*, s.admission_no, s.name AS student_name FROM fee_invoices fi JOIN students s ON s.id = fi.student_id';
        $params = [];
        if (in_array($user['role'], ['parent', 'student'], true)) {
            $sql .= ' WHERE s.id = ?';
            $params[] = (int)($user['linked_student_id'] ?? 0);
        }
        $sql .= ' ORDER BY fi.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function paymentsForInvoice(int $invoiceId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM payments WHERE invoice_id = ? ORDER BY date DESC');
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    public function summary(): array
    {
        return $this->db->query('SELECT COALESCE(SUM(amount_due),0) due_total, COALESCE(SUM(amount_due - balance),0) paid_total, COALESCE(SUM(balance),0) balance_total FROM fee_invoices')->fetch();
    }

    public function collectionSummary(): array
    {
        return $this->db->query('SELECT term, COUNT(*) AS invoice_count, COALESCE(SUM(amount_due),0) AS amount_due, COALESCE(SUM(amount_due - balance),0) AS paid, COALESCE(SUM(balance),0) AS balance FROM fee_invoices GROUP BY term ORDER BY term DESC')->fetchAll();
    }

    private function activeStudentExists(int $studentId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM students WHERE id = ? AND is_active = 1');
        $stmt->execute([$studentId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}

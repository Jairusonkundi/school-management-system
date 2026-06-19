<?php
namespace App\Models;

use App\Core\Model;

class Finance extends Model
{
    public function recordPayment(array $data): void
    {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('INSERT INTO payments (fee_id, student_id, amount, method, reference, received_by) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['fee_id'], $data['student_id'], $data['amount'], $data['method'], $data['reference'], $data['received_by']]);
        $update = $this->db->prepare('UPDATE fees SET amount_paid = amount_paid + ? WHERE id = ?');
        $update->execute([$data['amount'], $data['fee_id']]);
        $this->db->commit();
    }

    public function summary(): array
    {
        return $this->db->query('SELECT COALESCE(SUM(amount_due),0) due_total, COALESCE(SUM(amount_paid),0) paid_total, COALESCE(SUM(balance),0) balance_total FROM fees')->fetch();
    }

    public function feeAccounts(): array
    {
        return $this->db->query('SELECT f.*, s.admission_no, s.first_name, s.last_name FROM fees f JOIN students s ON s.id=f.student_id ORDER BY f.academic_year DESC, f.term DESC')->fetchAll();
    }
}

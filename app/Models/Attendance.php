<?php
namespace App\Models;

use App\Core\Model;

class Attendance extends Model
{
    public function mark(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO attendance (student_id, class_id, marked_by, attendance_date, status, note) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=VALUES(status), note=VALUES(note), marked_by=VALUES(marked_by)');
        $stmt->execute([$data['student_id'], $data['class_id'], $data['marked_by'], $data['attendance_date'], $data['status'], $data['note']]);
    }

    public function todayStats(): array
    {
        $stmt = $this->db->prepare('SELECT status, COUNT(*) total FROM attendance WHERE attendance_date = CURDATE() GROUP BY status');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

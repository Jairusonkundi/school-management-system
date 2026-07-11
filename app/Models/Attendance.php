<?php
namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class Attendance extends Model
{
    public function mark(array $data, array $user): array
    {
        $studentId = (int)($data['student_id'] ?? 0);
        if (!(new Student())->canAccessStudent($user, $studentId)) {
            throw new InvalidArgumentException('You are not assigned to record attendance for this student.');
        }
        if (!in_array($data['status'] ?? '', ['present', 'absent', 'excused'], true)) {
            throw new InvalidArgumentException('Invalid attendance status.');
        }
        if (!$this->validDate($data['date'] ?? '')) {
            throw new InvalidArgumentException('Enter a valid attendance date.');
        }
        if (($data['status'] ?? '') === 'excused' && trim($data['note'] ?? '') === '') {
            throw new InvalidArgumentException('Add a note when marking an excused absence.');
        }

        $stmt = $this->db->prepare('INSERT INTO attendance (student_id, date, status, recorded_by, note) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note), recorded_by = VALUES(recorded_by)');
        $stmt->execute([
            $studentId,
            $data['date'],
            $data['status'],
            $user['id'],
            trim($data['note'] ?? ''),
        ]);

        return (new Student())->find($studentId) ?? [];
    }

    public function history(?int $classId, ?string $from, ?string $to, array $user): array
    {
        if ($from !== null && $from !== '' && !$this->validDate($from)) {
            throw new InvalidArgumentException('Enter a valid start date.');
        }
        if ($to !== null && $to !== '' && !$this->validDate($to)) {
            throw new InvalidArgumentException('Enter a valid end date.');
        }
        if ($from && $to && $from > $to) {
            throw new InvalidArgumentException('Start date cannot be after end date.');
        }
        $sql = 'SELECT a.*, s.admission_no, s.name AS student_name, c.name AS class_name, c.stream, u.name AS recorded_by_name
                FROM attendance a
                JOIN students s ON s.id = a.student_id
                JOIN classes c ON c.id = s.class_id
                JOIN users u ON u.id = a.recorded_by
                WHERE 1 = 1';
        $params = [];

        if ($classId) {
            $sql .= ' AND s.class_id = ?';
            $params[] = $classId;
        }
        if ($from) {
            $sql .= ' AND a.date >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND a.date <= ?';
            $params[] = $to;
        }
        if ($user['role'] === 'teacher') {
            $sql .= ' AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.teacher_user_id = ? AND ta.class_id = s.class_id)';
            $params[] = $user['id'];
        } elseif (in_array($user['role'], ['parent', 'student'], true)) {
            $sql .= ' AND s.id = ?';
            $params[] = (int)($user['linked_student_id'] ?? 0);
        }

        $sql .= ' ORDER BY a.date DESC, s.name LIMIT 500';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function todayStats(): array
    {
        $stmt = $this->db->prepare('SELECT status, COUNT(*) total FROM attendance WHERE date = CURDATE() GROUP BY status');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function summaryByClass(): array
    {
        return $this->db->query("SELECT c.name, c.stream, COALESCE(SUM(a.status = 'present'),0) AS present_count, COALESCE(SUM(a.status = 'absent'),0) AS absent_count, COALESCE(SUM(a.status = 'excused'),0) AS excused_count FROM classes c LEFT JOIN students s ON s.class_id = c.id LEFT JOIN attendance a ON a.student_id = s.id GROUP BY c.id, c.name, c.stream ORDER BY c.name, c.stream")->fetchAll();
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}

<?php
namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class Academic extends Model
{
    public function computeGrade(float $marks): string
    {
        return match (true) {
            $marks >= 80 => 'EE',
            $marks >= 50 => 'ME',
            $marks >= 30 => 'AE',
            default => 'BE',
        };
    }

    public function recordResult(array $data, array $user): void
    {
        $studentId = (int)($data['student_id'] ?? 0);
        $subjectId = (int)($data['subject_id'] ?? 0);
        $marks = (float)($data['marks'] ?? -1);
        if ($marks < 0 || $marks > 100) {
            throw new InvalidArgumentException('Marks must be between 0 and 100.');
        }
        if (!$this->subjectExists($subjectId)) {
            throw new InvalidArgumentException('Select a valid subject.');
        }
        if (trim($data['term'] ?? '') === '') {
            throw new InvalidArgumentException('Term is required.');
        }
        if (!(new Student())->canAccessStudent($user, $studentId, $subjectId)) {
            throw new InvalidArgumentException('You are not assigned to record marks for this student or subject.');
        }

        $stmt = $this->db->prepare('INSERT INTO exam_results (student_id, subject_id, term, marks, grade, recorded_by) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks = VALUES(marks), grade = VALUES(grade), recorded_by = VALUES(recorded_by)');
        $stmt->execute([
            $studentId,
            $subjectId,
            trim($data['term']),
            $marks,
            $this->computeGrade($marks),
            $user['id'],
        ]);
    }

    public function results(?int $classId, ?string $term, array $user): array
    {
        $term = trim((string)$term);
        $sql = 'SELECT er.*, s.admission_no, s.name AS student_name, c.name AS class_name, c.stream, sub.name AS subject_name,
                       AVG(er.marks) OVER (PARTITION BY s.class_id, er.subject_id, er.term) AS class_average,
                       RANK() OVER (PARTITION BY s.class_id, er.subject_id, er.term ORDER BY er.marks DESC) AS class_rank
                FROM exam_results er
                JOIN students s ON s.id = er.student_id
                JOIN classes c ON c.id = s.class_id
                JOIN subjects sub ON sub.id = er.subject_id
                WHERE 1 = 1';
        $params = [];
        if ($classId) {
            $sql .= ' AND s.class_id = ?';
            $params[] = $classId;
        }
        if ($term !== '') {
            $sql .= ' AND er.term = ?';
            $params[] = $term;
        }
        if ($user['role'] === 'teacher') {
            $sql .= ' AND EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.teacher_user_id = ? AND ta.class_id = s.class_id AND (ta.subject_id = er.subject_id OR ta.subject_id IS NULL))';
            $params[] = $user['id'];
        } elseif (in_array($user['role'], ['parent', 'student'], true)) {
            $sql .= ' AND s.id = ?';
            $params[] = (int)($user['linked_student_id'] ?? 0);
        }
        $sql .= ' ORDER BY er.term DESC, c.name, sub.name, class_rank LIMIT 500';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function performanceSummary(): array
    {
        return $this->db->query('SELECT c.name, c.stream, sub.name AS subject_name, er.term, AVG(er.marks) AS average_marks, MAX(er.marks) AS highest_marks, MIN(er.marks) AS lowest_marks FROM exam_results er JOIN students s ON s.id = er.student_id JOIN classes c ON c.id = s.class_id JOIN subjects sub ON sub.id = er.subject_id GROUP BY c.id, sub.id, er.term ORDER BY er.term DESC, c.name, sub.name')->fetchAll();
    }

    private function subjectExists(int $subjectId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM subjects WHERE id = ?');
        $stmt->execute([$subjectId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

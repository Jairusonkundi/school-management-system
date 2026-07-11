<?php
namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class TeacherAssignment extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT ta.*, u.name AS teacher_name, c.name AS class_name, c.stream, sub.name AS subject_name FROM teacher_assignments ta JOIN users u ON u.id = ta.teacher_user_id JOIN classes c ON c.id = ta.class_id LEFT JOIN subjects sub ON sub.id = ta.subject_id ORDER BY u.name, c.name, sub.name')->fetchAll();
    }

    public function create(array $data): void
    {
        $teacherId = (int)($data['teacher_user_id'] ?? 0);
        $classId = (int)($data['class_id'] ?? 0);
        $subjectId = empty($data['subject_id']) ? null : (int)$data['subject_id'];
        if (!$this->activeTeacherExists($teacherId)) {
            throw new InvalidArgumentException('Select an active teacher account.');
        }
        if (!$this->classExists($classId)) {
            throw new InvalidArgumentException('Select a valid class.');
        }
        if ($subjectId !== null && !$this->subjectExists($subjectId)) {
            throw new InvalidArgumentException('Select a valid subject.');
        }
        $stmt = $this->db->prepare('INSERT IGNORE INTO teacher_assignments (teacher_user_id, class_id, subject_id) VALUES (?, ?, ?)');
        $stmt->execute([$teacherId, $classId, $subjectId]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM teacher_assignments WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function activeTeacherExists(int $teacherId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'teacher' AND is_active = 1");
        $stmt->execute([$teacherId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function classExists(int $classId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM classes WHERE id = ?');
        $stmt->execute([$classId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function subjectExists(int $subjectId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM subjects WHERE id = ?');
        $stmt->execute([$subjectId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

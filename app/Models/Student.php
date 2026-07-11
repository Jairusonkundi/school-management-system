<?php
namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class Student extends Model
{
    public function generateAdmissionNo(): string
    {
        $stmt = $this->db->prepare('SELECT next_number FROM admission_sequences WHERE id = 1 FOR UPDATE');
        $stmt->execute();
        $next = (int)$stmt->fetchColumn();
        $update = $this->db->prepare('UPDATE admission_sequences SET next_number = next_number + 1 WHERE id = 1');
        $update->execute();
        return 'SKA-' . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }

    public function create(array $data): int
    {
        $this->validate($data);
        $this->db->beginTransaction();
        try {
            $admissionNo = $this->generateAdmissionNo();
            $stmt = $this->db->prepare('INSERT INTO students (admission_no, name, class_id, guardian_user_id, medical_notes, discipline_notes) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $admissionNo,
                trim($data['name']),
                (int)$data['class_id'],
                $this->nullableInt($data['guardian_user_id'] ?? null),
                trim($data['medical_notes'] ?? ''),
                trim($data['discipline_notes'] ?? ''),
            ]);
            $studentId = (int)$this->db->lastInsertId();
            if (!empty($data['guardian_user_id'])) {
                (new User())->assignStudent((int)$data['guardian_user_id'], $studentId);
            }
            $this->db->commit();
            return $studentId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function all(): array
    {
        return $this->db->query('SELECT s.*, c.name AS class_name, c.stream, u.name AS guardian_name, u.email AS guardian_email FROM students s JOIN classes c ON c.id = s.class_id LEFT JOIN users u ON u.id = s.guardian_user_id ORDER BY s.id DESC')->fetchAll();
    }

    public function accessibleForUser(array $user): array
    {
        if ($user['role'] === 'admin') {
            return $this->all();
        }
        if ($user['role'] === 'teacher') {
            $stmt = $this->db->prepare('SELECT DISTINCT s.*, c.name AS class_name, c.stream FROM students s JOIN classes c ON c.id = s.class_id JOIN teacher_assignments ta ON ta.class_id = s.class_id WHERE ta.teacher_user_id = ? AND s.is_active = 1 ORDER BY s.name');
            $stmt->execute([$user['id']]);
            return $stmt->fetchAll();
        }
        $studentId = (int)($user['linked_student_id'] ?? 0);
        if ($studentId < 1) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT s.*, c.name AS class_name, c.stream FROM students s JOIN classes c ON c.id = s.class_id WHERE s.id = ?');
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT s.*, c.name AS class_name, c.stream, u.email AS guardian_email, u.name AS guardian_name FROM students s JOIN classes c ON c.id = s.class_id LEFT JOIN users u ON u.id = s.guardian_user_id WHERE s.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function countsByClass(): array
    {
        return $this->db->query('SELECT c.name, c.stream, COUNT(s.id) AS total FROM classes c LEFT JOIN students s ON s.class_id = c.id GROUP BY c.id, c.name, c.stream ORDER BY c.name, c.stream')->fetchAll();
    }

    public function canAccessStudent(array $user, int $studentId, ?int $subjectId = null): bool
    {
        if ($user['role'] === 'admin') {
            return true;
        }
        if (in_array($user['role'], ['parent', 'student'], true)) {
            return (int)($user['linked_student_id'] ?? 0) === $studentId;
        }
        if ($user['role'] === 'teacher') {
            $sql = 'SELECT COUNT(*) FROM students s JOIN teacher_assignments ta ON ta.class_id = s.class_id WHERE s.id = ? AND ta.teacher_user_id = ?';
            $params = [$studentId, $user['id']];
            if ($subjectId !== null) {
                $sql .= ' AND (ta.subject_id = ? OR ta.subject_id IS NULL)';
                $params[] = $subjectId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        }
        return false;
    }

    private function validate(array $data): void
    {
        if (trim($data['name'] ?? '') === '') {
            throw new InvalidArgumentException('Student name is required.');
        }
        $classId = (int)($data['class_id'] ?? 0);
        if ($classId < 1 || !$this->classExists($classId)) {
            throw new InvalidArgumentException('Class is required.');
        }
        $guardianId = $this->nullableInt($data['guardian_user_id'] ?? null);
        if ($guardianId !== null && !$this->activeParentExists($guardianId)) {
            throw new InvalidArgumentException('Guardian must be an active parent account.');
        }
    }

    private function nullableInt($value): ?int
    {
        return $value === null || $value === '' ? null : (int)$value;
    }

    private function classExists(int $classId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM classes WHERE id = ?');
        $stmt->execute([$classId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function activeParentExists(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'parent' AND is_active = 1");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

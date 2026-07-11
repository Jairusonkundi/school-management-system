<?php
namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db->query('SELECT u.*, s.admission_no, s.name AS student_name FROM users u LEFT JOIN students s ON s.id = u.linked_student_id ORDER BY u.role, u.name')->fetchAll();
    }

    public function create(array $data): int
    {
        $this->validate($data, true);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role, linked_student_id, is_active) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($data['name']),
            strtolower(trim($data['email'])),
            password_hash((string)$data['password'], PASSWORD_BCRYPT),
            $data['role'],
            $this->nullableInt($data['linked_student_id'] ?? null),
            !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->validate($data, false);
        $fields = 'name = ?, email = ?, role = ?, linked_student_id = ?, is_active = ?';
        $params = [
            trim($data['name']),
            strtolower(trim($data['email'])),
            $data['role'],
            $this->nullableInt($data['linked_student_id'] ?? null),
            !empty($data['is_active']) ? 1 : 0,
        ];

        if (!empty($data['password'])) {
            $fields .= ', password_hash = ?';
            $params[] = password_hash((string)$data['password'], PASSWORD_BCRYPT);
        }

        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE users SET {$fields} WHERE id = ?");
        $stmt->execute($params);
    }

    public function deactivate(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function assignStudent(int $userId, int $studentId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET linked_student_id = ? WHERE id = ?');
        $stmt->execute([$studentId, $userId]);
    }

    private function validate(array $data, bool $requirePassword): void
    {
        if (trim($data['name'] ?? '') === '') {
            throw new InvalidArgumentException('Name is required.');
        }
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email is required.');
        }
        if (!in_array($data['role'] ?? '', ['admin', 'teacher', 'parent', 'student'], true)) {
            throw new InvalidArgumentException('Invalid role selected.');
        }
        if ($requirePassword && strlen((string)($data['password'] ?? '')) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }
        $linkedStudentId = $this->nullableInt($data['linked_student_id'] ?? null);
        if ($linkedStudentId !== null && !in_array($data['role'], ['parent', 'student'], true)) {
            throw new InvalidArgumentException('Only parent and student accounts can be linked to a student.');
        }
        if ($linkedStudentId !== null && !$this->studentExists($linkedStudentId)) {
            throw new InvalidArgumentException('Linked student was not found.');
        }
    }

    private function nullableInt($value): ?int
    {
        return $value === null || $value === '' ? null : (int)$value;
    }

    private function studentExists(int $studentId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM students WHERE id = ? AND is_active = 1');
        $stmt->execute([$studentId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

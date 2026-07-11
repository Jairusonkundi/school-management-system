<?php
namespace App\Models;

use App\Core\Model;

class Lookup extends Model
{
    public function classes(): array
    {
        return $this->db->query('SELECT * FROM classes ORDER BY name, stream')->fetchAll();
    }

    public function classesForUser(array $user): array
    {
        if ($user['role'] === 'teacher') {
            $stmt = $this->db->prepare('SELECT DISTINCT c.* FROM classes c JOIN teacher_assignments ta ON ta.class_id = c.id WHERE ta.teacher_user_id = ? ORDER BY c.name, c.stream');
            $stmt->execute([$user['id']]);
            return $stmt->fetchAll();
        }
        return $this->classes();
    }

    public function subjects(): array
    {
        return $this->db->query('SELECT * FROM subjects ORDER BY name')->fetchAll();
    }

    public function subjectsForUser(array $user): array
    {
        if ($user['role'] === 'teacher') {
            $stmt = $this->db->prepare('SELECT DISTINCT sub.* FROM subjects sub WHERE EXISTS (SELECT 1 FROM teacher_assignments ta WHERE ta.teacher_user_id = ? AND (ta.subject_id = sub.id OR ta.subject_id IS NULL)) ORDER BY sub.name');
            $stmt->execute([$user['id']]);
            return $stmt->fetchAll();
        }
        return $this->subjects();
    }

    public function students(): array
    {
        return $this->db->query('SELECT s.id, s.admission_no, s.name, s.class_id, c.name AS class_name, c.stream FROM students s JOIN classes c ON c.id = s.class_id WHERE s.is_active = 1 ORDER BY s.name')->fetchAll();
    }

    public function usersByRole(string $role): array
    {
        $stmt = $this->db->prepare('SELECT id, name, email FROM users WHERE role = ? AND is_active = 1 ORDER BY name');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
}

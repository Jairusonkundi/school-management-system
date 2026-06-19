<?php
namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    public function generateAdmissionNo(string $section): string
    {
        $prefix = $section === 'junior' ? 'SKA-JS-' : 'SKA-SS-';
        do {
            $number = $prefix . date('Y') . '-' . random_int(1000, 9999);
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM students WHERE admission_no = ?');
            $stmt->execute([$number]);
        } while ((int)$stmt->fetchColumn() > 0);
        return $number;
    }

    public function create(array $data): int
    {
        $admissionNo = $this->generateAdmissionNo($data['school_section']);
        $stmt = $this->db->prepare('INSERT INTO students (admission_no, first_name, last_name, gender, date_of_birth, school_section, class_id, parent_id, admission_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$admissionNo, $data['first_name'], $data['last_name'], $data['gender'], $data['date_of_birth'], $data['school_section'], $data['class_id'], $data['parent_id'], $data['admission_date']]);
        return (int)$this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query('SELECT s.*, c.name class_name, c.stream FROM students s JOIN classes c ON c.id=s.class_id ORDER BY s.id DESC')->fetchAll();
    }

    public function countsBySection(): array
    {
        return $this->db->query("SELECT school_section, COUNT(*) total FROM students WHERE status='active' GROUP BY school_section")->fetchAll();
    }
}

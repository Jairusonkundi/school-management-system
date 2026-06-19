<?php
namespace App\Models;

use App\Core\Model;

class Lookup extends Model
{
    public function classes(): array { return $this->db->query('SELECT * FROM classes ORDER BY school_section, level_number')->fetchAll(); }
    public function parents(): array { return $this->db->query('SELECT * FROM parents ORDER BY id DESC')->fetchAll(); }
    public function subjects(): array { return $this->db->query('SELECT * FROM subjects ORDER BY name')->fetchAll(); }
    public function learningAreas(): array { return $this->db->query('SELECT * FROM junior_learning_areas ORDER BY name')->fetchAll(); }
    public function teachers(): array { return $this->db->query('SELECT t.*, u.name FROM teachers t JOIN users u ON u.id=t.user_id ORDER BY u.name')->fetchAll(); }
    public function students(): array { return $this->db->query('SELECT id, admission_no, first_name, last_name, class_id FROM students ORDER BY first_name')->fetchAll(); }
}

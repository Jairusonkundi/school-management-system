<?php
namespace App\Models;

use App\Core\Model;

class Academic extends Model
{
    public function computeGrade(float $total): string
    {
        return match (true) { $total >= 80 => 'A', $total >= 75 => 'A-', $total >= 70 => 'B+', $total >= 65 => 'B', $total >= 60 => 'B-', $total >= 55 => 'C+', $total >= 50 => 'C', $total >= 45 => 'C-', $total >= 40 => 'D+', $total >= 35 => 'D', default => 'E' };
    }

    public function recordGrade(array $data): void
    {
        $letter = $this->computeGrade((float)$data['cat_score'] + (float)$data['exam_score']);
        $stmt = $this->db->prepare('INSERT INTO grades (student_id, subject_id, teacher_id, term, academic_year, cat_score, exam_score, grade_letter, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE cat_score=VALUES(cat_score), exam_score=VALUES(exam_score), grade_letter=VALUES(grade_letter), remarks=VALUES(remarks)');
        $stmt->execute([$data['student_id'], $data['subject_id'], $data['teacher_id'], $data['term'], $data['academic_year'], $data['cat_score'], $data['exam_score'], $letter, $data['remarks']]);
    }

    public function recordCbc(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO cbc_assessments (student_id, learning_area_id, teacher_id, term, academic_year, competency, descriptor, remarks, assessed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['student_id'], $data['learning_area_id'], $data['teacher_id'], $data['term'], $data['academic_year'], $data['competency'], $data['descriptor'], $data['remarks'], $data['assessed_at']]);
    }
}

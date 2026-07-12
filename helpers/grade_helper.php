<?php
const CBC_GRADE_SCALE = [
    'EE' => ['min' => 80, 'label' => 'Exceeding Expectation', 'description' => 'Learner exceeds expected competency'],
    'ME' => ['min' => 50, 'label' => 'Meeting Expectation', 'description' => 'Learner meets expected competency'],
    'AE' => ['min' => 30, 'label' => 'Approaching Expectation', 'description' => 'Learner is approaching expected competency'],
    'BE' => ['min' => 0, 'label' => 'Below Expectation', 'description' => 'Learner is below expected competency'],
];

function calculate_grade(float $marks): string
{
    foreach (CBC_GRADE_SCALE as $code => $band) {
        if ($marks >= $band['min']) {
            return $code;
        }
    }
    return 'BE';
}

function grade_remark(float $marks): string
{
    return CBC_GRADE_SCALE[calculate_grade($marks)]['label'];
}

function grade_description(float $marks): string
{
    return CBC_GRADE_SCALE[calculate_grade($marks)]['description'];
}

function cbc_grade_display(?string $code): string
{
    return isset(CBC_GRADE_SCALE[$code]) ? $code . ' - ' . CBC_GRADE_SCALE[$code]['label'] : '';
}

function is_numeric_assessment_level(?string $levelName): bool
{
    return in_array($levelName, ['Upper Primary', 'Junior Secondary School'], true);
}

function cbc_level_options(): array
{
    return [
        'EE' => 'Exceeding Expectation',
        'ME' => 'Meeting Expectation',
        'AE' => 'Approaching Expectation',
        'BE' => 'Below Expectation',
    ];
}

function competency_tally(array $results): string
{
    $counts = ['EE' => 0, 'ME' => 0, 'AE' => 0, 'BE' => 0];
    foreach ($results as $result) {
        $code = $result['grade'] ?? null;
        if (isset($counts[$code])) {
            $counts[$code]++;
        }
    }
    $parts = [];
    foreach ($counts as $code => $count) {
        if ($count > 0) {
            $parts[] = $count . ' learning area' . ($count === 1 ? '' : 's') . ' at ' . $code;
        }
    }
    return $parts ? implode(', ', $parts) : 'No competency levels recorded';
}

function update_result_grade(PDO $pdo, int $resultId, float $marks): void
{
    $stmt = $pdo->prepare('UPDATE exam_results SET grade = ? WHERE result_id = ?');
    $stmt->execute([calculate_grade($marks), $resultId]);
}

function get_class_ranking(PDO $pdo, int $class_id, int $term, string $year): array
{
    $levelStmt = $pdo->prepare('SELECT el.level_name FROM classes c JOIN education_levels el ON el.level_id = c.level_id WHERE c.class_id = ?');
    $levelStmt->execute([$class_id]);
    if (!is_numeric_assessment_level((string)$levelStmt->fetchColumn())) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT s.student_id, s.full_name, s.admission_no, SUM(er.marks) AS total_marks,
                AVG(er.marks) AS average_marks,
                RANK() OVER (ORDER BY SUM(er.marks) DESC) AS rank_position
         FROM students s
         JOIN exam_results er ON er.student_id = s.student_id
         WHERE er.class_id = ? AND er.term = ? AND er.academic_year = ?
         GROUP BY s.student_id, s.full_name, s.admission_no
         ORDER BY total_marks DESC'
    );
    $stmt->execute([$class_id, $term, $year]);
    return $stmt->fetchAll();
}

<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../helpers/grade_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher','parent','student']);

$pageTitle = 'Report Card';
$studentId = (int)($_GET['student_id'] ?? 0);
$term = (int)($_GET['term'] ?? current_term());
$year = (string)($_GET['academic_year'] ?? current_academic_year());
$scope = 'WHERE s.is_active = 1';
$params = [];
if (get_current_role() === 'parent') {
    $scope .= ' AND s.guardian_id = ?';
    $params[] = get_current_user_id();
}
if (get_current_role() === 'student') {
    $scope .= ' AND s.user_id = ?';
    $params[] = get_current_user_id();
}

$stmt = $pdo->prepare("SELECT s.student_id, s.full_name, s.admission_no, c.grade_name, c.stream_name FROM students s JOIN classes c ON c.class_id = s.class_id {$scope} ORDER BY s.full_name");
$stmt->execute($params);
$students = $stmt->fetchAll();
if (!$studentId && $students) {
    $studentId = (int)$students[0]['student_id'];
}

$student = null;
$results = [];
$att = ['present_days' => 0, 'absent_days' => 0, 'total_days' => 0];
$rank = '';
$isNumericAssessment = true;

if ($studentId) {
    $stmt = $pdo->prepare('SELECT s.*, c.class_name, c.grade_name, c.stream_name, el.level_name
        FROM students s
        JOIN classes c ON c.class_id = s.class_id
        JOIN education_levels el ON el.level_id = c.level_id
        WHERE s.student_id = ?');
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
    $isNumericAssessment = is_numeric_assessment_level($student['level_name'] ?? null);

    $stmt = $pdo->prepare('SELECT sub.subject_name, er.marks, er.grade, er.comment
        FROM exam_results er
        JOIN subjects sub ON sub.subject_id = er.subject_id
        WHERE er.student_id = ? AND er.term = ? AND er.academic_year = ?
        ORDER BY sub.subject_name');
    $stmt->execute([$studentId, $term, $year]);
    $results = $stmt->fetchAll();

    if ($isNumericAssessment) {
        $stmt = $pdo->prepare("SELECT SUM(status='Present') present_days, SUM(status='Absent') absent_days, COUNT(*) total_days FROM attendance WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $att = $stmt->fetch();
        foreach (get_class_ranking($pdo, (int)$student['class_id'], $term, $year) as $r) {
            if ((int)$r['student_id'] === $studentId) {
                $rank = $r['rank_position'];
            }
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>
<form class="row g-2 mb-3 no-print">
    <div class="col-md-5"><select class="form-select" name="student_id"><?php foreach ($students as $s): ?><option value="<?= (int)$s['student_id'] ?>"<?= selected_attr($studentId, $s['student_id']) ?>><?= h($s['admission_no'] . ' - ' . $s['full_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select class="form-select" name="term"><option<?= selected_attr($term, 1) ?>>1</option><option<?= selected_attr($term, 2) ?>>2</option><option<?= selected_attr($term, 3) ?>>3</option></select></div>
    <div class="col-md-2"><input class="form-control" name="academic_year" value="<?= h($year) ?>"></div>
    <div class="col-md-3"><button class="btn btn-primary">Load</button> <button type="button" onclick="print()" class="btn btn-secondary">Print</button></div>
</form>
<?php if ($student): ?>
    <div class="text-center mb-3">
        <h2>Sunshine Kaseveni Academy</h2>
        <p><?= h($student['full_name']) ?> | <?= h($student['admission_no']) ?> | <?= h(class_label($student)) ?> | Term <?= h($term) ?> <?= h($year) ?></p>
    </div>
    <?php if ($isNumericAssessment): $total = array_sum(array_column($results, 'marks')); $avg = count($results) ? $total / count($results) : 0; $avgCode = calculate_grade($avg); ?>
        <table class="table"><thead><tr><th>Subject</th><th>Marks</th><th>CBC Level</th></tr></thead><tbody>
        <?php foreach ($results as $r): ?><tr><td><?= h($r['subject_name']) ?></td><td><?= h($r['marks']) ?></td><td><span class="badge <?= h(cbc_badge_class($r['grade'])) ?>"><?= h(cbc_grade_label($r['grade'])) ?></span></td></tr><?php endforeach; ?>
        <tr><th>Total / Average / Rank</th><th><?= h($total) ?> / <?= h(round($avg, 1)) ?></th><th><span class="badge <?= h(cbc_badge_class($avgCode)) ?>"><?= h(cbc_grade_label($avgCode)) ?></span> | Rank <?= h($rank) ?></th></tr>
        </tbody></table>
        <p>Attendance: Present <?= (int)$att['present_days'] ?>, Absent <?= (int)$att['absent_days'] ?>, Rate <?= (int)$att['total_days'] > 0 ? h(round(((int)$att['present_days'] / (int)$att['total_days']) * 100, 1)) : 0 ?>%</p>
    <?php else: ?>
        <table class="table"><thead><tr><th>Learning Area</th><th>Competency Level</th><th>Teacher's Comment</th></tr></thead><tbody>
        <?php foreach ($results as $r): ?><tr><td><?= h($r['subject_name']) ?></td><td><span class="badge <?= h(cbc_badge_class($r['grade'])) ?>"><?= h(cbc_grade_label($r['grade'])) ?></span></td><td><?= h($r['comment']) ?></td></tr><?php endforeach; ?>
        <tr><th>Summary</th><td colspan="2"><?= h(competency_tally($results)) ?></td></tr>
        </tbody></table>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

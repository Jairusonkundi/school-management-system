<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../helpers/grade_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher']);

$pageTitle = 'Class Mark Sheet';
$classId = (int)($_GET['class_id'] ?? 0);
$term = (int)($_GET['term'] ?? current_term());
$year = (string)($_GET['academic_year'] ?? current_academic_year());
$classes = fetch_classes($pdo);
$subjects = [];
$rows = [];
$isNumericAssessment = true;

if ($classId) {
    $stmt = $pdo->prepare('SELECT c.*, el.level_name FROM classes c JOIN education_levels el ON el.level_id = c.level_id WHERE c.class_id = ?');
    $stmt->execute([$classId]);
    $classInfo = $stmt->fetch();
    $isNumericAssessment = is_numeric_assessment_level($classInfo['level_name'] ?? null);

    $stmt = $pdo->prepare('SELECT DISTINCT sub.subject_id, sub.subject_name FROM subjects sub JOIN exam_results er ON er.subject_id = sub.subject_id WHERE er.class_id = ? AND er.term = ? AND er.academic_year = ? ORDER BY sub.subject_name');
    $stmt->execute([$classId, $term, $year]);
    $subjects = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT s.student_id, s.full_name, sub.subject_name, er.marks, er.grade, er.comment
        FROM students s
        LEFT JOIN exam_results er ON er.student_id = s.student_id AND er.term = ? AND er.academic_year = ?
        LEFT JOIN subjects sub ON sub.subject_id = er.subject_id
        WHERE s.class_id = ?
        ORDER BY s.full_name, sub.subject_name');
    $stmt->execute([$term, $year, $classId]);
    foreach ($stmt->fetchAll() as $r) {
        $rows[$r['student_id']]['name'] = $r['full_name'];
        if ($r['subject_name']) {
            $rows[$r['student_id']]['results'][$r['subject_name']] = $r;
        }
    }
    $ranking = $isNumericAssessment ? get_class_ranking($pdo, $classId, $term, $year) : [];
}

require __DIR__ . '/../../includes/header.php';
?>
<form class="row g-2 mb-3 no-print">
    <div class="col-md-4"><select class="form-select" name="class_id"><?php foreach ($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"<?= selected_attr($classId, $c['class_id']) ?>><?= h(class_label($c)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select class="form-select" name="term"><option<?= selected_attr($term, 1) ?>>1</option><option<?= selected_attr($term, 2) ?>>2</option><option<?= selected_attr($term, 3) ?>>3</option></select></div>
    <div class="col-md-2"><input class="form-control" name="academic_year" value="<?= h($year) ?>"></div>
    <div class="col-md-4"><button class="btn btn-primary">Load</button> <button type="button" class="btn btn-secondary" onclick="print()">Print</button></div>
</form>
<table class="table">
    <thead><tr><th>Student</th><?php foreach ($subjects as $s): ?><th><?= h($s['subject_name']) ?></th><?php endforeach; ?><?php if ($isNumericAssessment): ?><th>Total</th><th>Average</th><th>CBC Level</th><th>Rank</th><?php else: ?><th>Competency Summary</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($rows as $sid => $r): $results = $r['results'] ?? []; ?>
        <tr>
            <td><?= h($r['name']) ?></td>
            <?php foreach ($subjects as $s): $result = $results[$s['subject_name']] ?? null; ?>
                <td>
                    <?php if ($isNumericAssessment): ?>
                        <?= h($result['marks'] ?? '') ?>
                    <?php elseif ($result): ?>
                        <span class="badge <?= h(cbc_badge_class($result['grade'])) ?>"><?= h($result['grade']) ?></span>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
            <?php if ($isNumericAssessment): $marks = array_filter(array_column($results, 'marks'), static fn($m) => $m !== null && $m !== ''); $total = array_sum($marks); $avg = count($marks) ? $total / count($marks) : 0; $rank = ''; foreach (($ranking ?? []) as $rk) { if ((int)$rk['student_id'] === (int)$sid) { $rank = $rk['rank_position']; } } $code = calculate_grade($avg); ?>
                <td><?= h($total) ?></td><td><?= h(round($avg, 1)) ?></td><td><span class="badge <?= h(cbc_badge_class($code)) ?>"><?= h(cbc_grade_label($code)) ?></span></td><td><?= h($rank) ?></td>
            <?php else: ?>
                <td><?= h(competency_tally(array_values($results))) ?></td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

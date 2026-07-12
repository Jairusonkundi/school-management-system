<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../helpers/grade_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_any_role(['admin','teacher']);

$pageTitle = 'Enter Assessment';
$classId = (int)($_GET['class_id'] ?? $_POST['class_id'] ?? 0);
$subjectId = (int)($_GET['subject_id'] ?? $_POST['subject_id'] ?? 0);
$term = (int)($_GET['term'] ?? $_POST['term'] ?? current_term());
$year = (string)($_GET['academic_year'] ?? $_POST['academic_year'] ?? current_academic_year());
$message = '';

$classes = get_current_role() === 'teacher' ? fetch_classes($pdo, null, get_current_user_id()) : fetch_classes($pdo);
$classInfo = null;
if ($classId > 0) {
    $stmt = $pdo->prepare('SELECT c.*, el.level_name FROM classes c JOIN education_levels el ON el.level_id = c.level_id WHERE c.class_id = ?');
    $stmt->execute([$classId]);
    $classInfo = $stmt->fetch();
}
$isNumericAssessment = is_numeric_assessment_level($classInfo['level_name'] ?? null);

$subjects = [];
if ($classId > 0) {
    $stmt = $pdo->prepare('SELECT sub.*
        FROM subjects sub
        LEFT JOIN subject_levels sl ON sl.subject_id = sub.subject_id
        JOIN classes c ON c.class_id = ?
        WHERE (sl.level_id = c.level_id OR sub.class_id = c.class_id)
        GROUP BY sub.subject_id, sub.subject_name, sub.offered_grades, sub.section, sub.class_id
        ORDER BY sub.subject_name');
    $stmt->execute([$classId]);
    $subjects = $stmt->fetchAll();
    if ($subjectId === 0 && $subjects) {
        $subjectId = (int)$subjects[0]['subject_id'];
    }
} else {
    $subjects = $pdo->query('SELECT * FROM subjects ORDER BY subject_name')->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if ($isNumericAssessment && isset($_POST['marks'])) {
        foreach ($_POST['marks'] as $sid => $marks) {
            if ($marks === '') {
                continue;
            }
            $m = max(0, min(100, (float)$marks));
            $grade = calculate_grade($m);
            $comment = trim((string)($_POST['comments'][$sid] ?? ''));
            $stmt = $pdo->prepare('INSERT INTO exam_results (student_id, subject_id, class_id, term, academic_year, marks, grade, comment, entered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks=VALUES(marks), grade=VALUES(grade), comment=VALUES(comment), entered_by=VALUES(entered_by)');
            $stmt->execute([(int)$sid, $subjectId, $classId, $term, $year, $m, $grade, $comment, get_current_user_id()]);
        }
    } elseif (!$isNumericAssessment && isset($_POST['levels'])) {
        foreach ($_POST['levels'] as $sid => $levelCode) {
            if (!isset(cbc_level_options()[$levelCode])) {
                continue;
            }
            $comment = trim((string)($_POST['comments'][$sid] ?? ''));
            $stmt = $pdo->prepare('INSERT INTO exam_results (student_id, subject_id, class_id, term, academic_year, marks, grade, comment, entered_by) VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?) ON DUPLICATE KEY UPDATE marks=NULL, grade=VALUES(grade), comment=VALUES(comment), entered_by=VALUES(entered_by)');
            $stmt->execute([(int)$sid, $subjectId, $classId, $term, $year, $levelCode, $comment, get_current_user_id()]);
        }
    }
    $message = 'Assessment saved.';
}

$students = [];
if ($classId > 0 && $subjectId > 0) {
    $stmt = $pdo->prepare('SELECT s.*, er.marks, er.grade, er.comment
        FROM students s
        LEFT JOIN exam_results er ON er.student_id = s.student_id AND er.subject_id = ? AND er.term = ? AND er.academic_year = ?
        WHERE s.class_id = ? AND s.is_active = 1
        ORDER BY s.full_name');
    $stmt->execute([$subjectId, $term, $year, $classId]);
    $students = $stmt->fetchAll();
}

require __DIR__ . '/../../includes/header.php';
?>
<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-3"><select class="form-select" name="class_id"><?php foreach ($classes as $c): ?><option value="<?= (int)$c['class_id'] ?>"<?= selected_attr($classId, $c['class_id']) ?>><?= h(class_label($c)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><select class="form-select" name="subject_id"><?php foreach ($subjects as $s): ?><option value="<?= (int)$s['subject_id'] ?>"<?= selected_attr($subjectId, $s['subject_id']) ?>><?= h($s['subject_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select class="form-select" name="term"><option<?= selected_attr($term, 1) ?>>1</option><option<?= selected_attr($term, 2) ?>>2</option><option<?= selected_attr($term, 3) ?>>3</option></select></div>
    <div class="col-md-2"><input class="form-control" name="academic_year" value="<?= h($year) ?>"></div>
    <div class="col-md-2"><button class="btn btn-primary">Load</button></div>
</form>
<?php if ($classInfo): ?><p class="text-muted">Assessment mode: <?= h($isNumericAssessment ? 'Numeric marks with CBC level calculation' : 'Descriptive competency levels') ?> for <?= h($classInfo['level_name']) ?>.</p><?php endif; ?>
<?php if ($students): ?>
<form method="post">
    <?= csrf_input() ?>
    <input type="hidden" name="class_id" value="<?= (int)$classId ?>">
    <input type="hidden" name="subject_id" value="<?= (int)$subjectId ?>">
    <input type="hidden" name="term" value="<?= (int)$term ?>">
    <input type="hidden" name="academic_year" value="<?= h($year) ?>">
    <table class="table">
        <thead><tr><th>Student</th><th><?= h($isNumericAssessment ? 'Marks' : 'Competency Level') ?></th><th>Teacher's Comment</th></tr></thead>
        <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= h($s['full_name']) ?></td>
                <td>
                    <?php if ($isNumericAssessment): ?>
                        <input class="form-control" type="number" min="0" max="100" step="0.01" name="marks[<?= (int)$s['student_id'] ?>]" value="<?= h($s['marks']) ?>">
                    <?php else: ?>
                        <select class="form-select" name="levels[<?= (int)$s['student_id'] ?>]">
                            <?php foreach (cbc_level_options() as $code => $label): ?>
                                <option value="<?= h($code) ?>"<?= selected_attr($s['grade'] ?: 'ME', $code) ?>><?= h($code . ' - ' . $label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </td>
                <td><input class="form-control" name="comments[<?= (int)$s['student_id'] ?>]" value="<?= h($s['comment']) ?>"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <button class="btn btn-success">Save Assessment</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

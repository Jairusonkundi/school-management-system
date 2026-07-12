<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_role('admin');

$pageTitle = 'Subjects';
$levels = education_levels($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM subjects WHERE subject_id = ?');
        $stmt->execute([(int)$_POST['subject_id']]);
    } else {
        $selectedLevels = array_map('intval', $_POST['level_ids'] ?? []);
        if (!$selectedLevels) {
            $selectedLevels = array_map(static fn($level) => (int)$level['level_id'], $levels);
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO subjects (subject_name, class_id) VALUES (?, ?)');
            $stmt->execute([
                trim((string)$_POST['subject_name']),
                $_POST['class_id'] !== '' ? (int)$_POST['class_id'] : null,
            ]);
            $subjectId = (int)$pdo->lastInsertId();
            $link = $pdo->prepare('INSERT IGNORE INTO subject_levels (subject_id, level_id) VALUES (?, ?)');
            foreach ($selectedLevels as $levelId) {
                $link->execute([$subjectId, $levelId]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

$subjects = $pdo->query(
    'SELECT sub.*, c.class_name, c.grade_name,
            GROUP_CONCAT(el.level_name ORDER BY el.level_order SEPARATOR ", ") AS level_names
     FROM subjects sub
     LEFT JOIN classes c ON c.class_id = sub.class_id
     LEFT JOIN subject_levels sl ON sl.subject_id = sub.subject_id
     LEFT JOIN education_levels el ON el.level_id = sl.level_id
     GROUP BY sub.subject_id, sub.subject_name, sub.offered_grades, sub.section, sub.class_id, c.class_name, c.grade_name
     ORDER BY sub.subject_name'
)->fetchAll();
$classes = fetch_classes($pdo);
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" class="card mb-3">
    <div class="card-body row g-3">
        <?= csrf_input() ?>
        <div class="col-md-4"><input class="form-control" name="subject_name" placeholder="Learning area" required></div>
        <div class="col-md-4">
            <?php foreach ($levels as $level): ?>
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="level_ids[]" value="<?= (int)$level['level_id'] ?>" checked>
                    <?= h($level['level_name']) ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="class_id">
                <option value="">All selected levels</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= (int)$c['class_id'] ?>"><?= h(class_label($c)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1"><button class="btn btn-primary">Add</button></div>
    </div>
</form>
<table class="table">
    <thead><tr><th>Learning Area</th><th>Education Levels</th><th>Class</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($subjects as $s): ?>
        <tr>
            <td><?= h($s['subject_name']) ?></td>
            <td><?= h($s['level_names'] ?: 'Not assigned') ?></td>
            <td><?= h($s['grade_name'] ?: 'All') ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Delete subject?')">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="subject_id" value="<?= (int)$s['subject_id'] ?>">
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

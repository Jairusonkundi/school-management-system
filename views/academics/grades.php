<h2>Exam Marks</h2>
<form class="card form-grid" method="post">
    <?= csrf_field() ?>
    <label>Student
        <select name="student_id" required>
            <?php foreach ($students as $s): ?>
                <option value="<?= e($s['id']) ?>"><?= e($s['admission_no'] . ' ' . $s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Subject
        <select name="subject_id" required>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= e($subject['id']) ?>"><?= e($subject['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Term<input name="term" placeholder="2026 Term 1" required></label>
    <label>Marks<input type="number" step="0.01" min="0" max="100" name="marks" required></label>
    <button>Save Marks</button>
</form>

<h3>Results, Class Average and Ranking</h3>
<form class="card form-grid" method="get">
    <input type="hidden" name="route" value="academics/grades">
    <label>Class
        <select name="class_id">
            <option value="">All assigned classes</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= e($class['id']) ?>" <?= (string)($_GET['class_id'] ?? '') === (string)$class['id'] ? 'selected' : '' ?>><?= e($class['name'] . ' ' . $class['stream']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Term<input name="term" value="<?= e($_GET['term'] ?? '') ?>"></label>
    <button>Filter</button>
</form>

<table>
<thead><tr><th>Term</th><th>Student</th><th>Class</th><th>Subject</th><th>Marks</th><th>CBC Level</th><th>Class Avg</th><th>Rank</th></tr></thead>
    <tbody>
    <?php foreach ($results as $row): ?>
        <tr>
            <td><?= e($row['term']) ?></td>
            <td><?= e($row['admission_no'] . ' ' . $row['student_name']) ?></td>
            <td><?= e($row['class_name'] . ' ' . $row['stream']) ?></td>
            <td><?= e($row['subject_name']) ?></td>
            <td><?= e($row['marks']) ?></td>
            <td><?= e(cbc_level_label($row['grade'])) ?></td>
            <td><?= number_format((float)$row['class_average'], 2) ?></td>
            <td><?= e($row['class_rank']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

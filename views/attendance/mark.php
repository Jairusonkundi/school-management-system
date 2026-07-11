<h2>Daily Attendance</h2>
<form class="card form-grid" method="post">
    <?= csrf_field() ?>
    <label>Student
        <select name="student_id" required>
            <?php foreach ($students as $s): ?>
                <option value="<?= e($s['id']) ?>"><?= e($s['admission_no'] . ' ' . $s['name'] . ' - ' . $s['class_name'] . ' ' . $s['stream']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Date<input type="date" name="date" value="<?= date('Y-m-d') ?>" required></label>
    <label>Status
        <select name="status">
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="excused">Excused</option>
        </select>
    </label>
    <label class="wide">Note<input name="note" placeholder="Required only when there is an explanation"></label>
    <button>Save Attendance</button>
</form>

<h3>Attendance History</h3>
<form class="card form-grid" method="get">
    <input type="hidden" name="route" value="attendance">
    <label>Class
        <select name="class_id">
            <option value="">All assigned classes</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= e($class['id']) ?>" <?= (string)($_GET['class_id'] ?? '') === (string)$class['id'] ? 'selected' : '' ?>><?= e($class['name'] . ' ' . $class['stream']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>From<input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>"></label>
    <label>To<input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>"></label>
    <button>Filter</button>
</form>

<table>
    <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Status</th><th>Recorded by</th><th>Note</th></tr></thead>
    <tbody>
    <?php foreach ($history as $row): ?>
        <tr>
            <td><?= e($row['date']) ?></td>
            <td><?= e($row['admission_no'] . ' ' . $row['student_name']) ?></td>
            <td><?= e($row['class_name'] . ' ' . $row['stream']) ?></td>
            <td><?= e(ucfirst($row['status'])) ?></td>
            <td><?= e($row['recorded_by_name']) ?></td>
            <td><?= e($row['note']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Teacher Assignments</h2>
<form class="card form-grid" method="post">
    <?= csrf_field() ?>
    <label>Teacher
        <select name="teacher_user_id" required>
            <?php foreach ($lookups->usersByRole('teacher') as $teacher): ?>
                <option value="<?= e($teacher['id']) ?>"><?= e($teacher['name'] . ' - ' . $teacher['email']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Class
        <select name="class_id" required>
            <?php foreach ($lookups->classes() as $class): ?>
                <option value="<?= e($class['id']) ?>"><?= e($class['name'] . ' ' . $class['stream']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Subject
        <select name="subject_id">
            <option value="">All subjects for class</option>
            <?php foreach ($lookups->subjects() as $subject): ?>
                <option value="<?= e($subject['id']) ?>"><?= e($subject['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button>Assign Teacher</button>
</form>

<table>
    <thead><tr><th>Teacher</th><th>Class</th><th>Subject Scope</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($assignments as $assignment): ?>
        <tr>
            <td><?= e($assignment['teacher_name']) ?></td>
            <td><?= e($assignment['class_name'] . ' ' . $assignment['stream']) ?></td>
            <td><?= e($assignment['subject_name'] ?? 'All subjects') ?></td>
            <td>
                <form class="inline" method="post" action="index.php?route=teacher-assignments/delete">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($assignment['id']) ?>">
                    <button>Remove</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Student Admission</h2>
<form class="card form-grid" method="post">
    <?= csrf_field() ?>
    <label>Student name<input name="name" required></label>
    <label>Class
        <select name="class_id" required>
            <?php foreach ($lookups->classes() as $class): ?>
                <option value="<?= e($class['id']) ?>"><?= e($class['name'] . ' ' . $class['stream']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Parent/Guardian
        <select name="guardian_user_id">
            <option value="">Not linked yet</option>
            <?php foreach ($lookups->usersByRole('parent') as $parent): ?>
                <option value="<?= e($parent['id']) ?>"><?= e($parent['name'] . ' - ' . $parent['email']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="wide">Medical notes<textarea name="medical_notes" rows="3"></textarea></label>
    <label class="wide">Discipline notes<textarea name="discipline_notes" rows="3"></textarea></label>
    <button>Register Student</button>
</form>

<h3>Recent Students</h3>
<table>
    <thead><tr><th>Admission No</th><th>Name</th><th>Class</th><th>Guardian</th></tr></thead>
    <tbody>
    <?php foreach ($students as $student): ?>
        <tr>
            <td><?= e($student['admission_no']) ?></td>
            <td><?= e($student['name']) ?></td>
            <td><?= e($student['class_name'] . ' ' . $student['stream']) ?></td>
            <td><?= e($student['guardian_name'] ?? 'Unlinked') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

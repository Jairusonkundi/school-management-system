<h2>User Accounts</h2>
<form class="card form-grid" method="post">
    <?= csrf_field() ?>
    <label>User ID for update<input type="number" name="id" placeholder="Leave blank for new user"></label>
    <label>Name<input name="name" required></label>
    <label>Email<input type="email" name="email" required></label>
    <label>Password<input type="password" name="password" placeholder="Required for new users"></label>
    <label>Role
        <select name="role">
            <?php foreach ($roles as $role): ?>
                <option value="<?= e($role) ?>"><?= e(ucfirst($role)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Linked student
        <select name="linked_student_id">
            <option value="">None</option>
            <?php foreach ($lookups->students() as $student): ?>
                <option value="<?= e($student['id']) ?>"><?= e($student['admission_no'] . ' ' . $student['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="check"><input type="checkbox" name="is_active" value="1" checked> Active</label>
    <button>Save User</button>
</form>

<table>
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Linked Student</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($users as $account): ?>
        <tr>
            <td><?= e($account['id']) ?></td>
            <td><?= e($account['name']) ?></td>
            <td><?= e($account['email']) ?></td>
            <td><?= e(ucfirst($account['role'])) ?></td>
            <td><?= e($account['admission_no'] ? $account['admission_no'] . ' ' . $account['student_name'] : '') ?></td>
            <td><?= $account['is_active'] ? 'Active' : 'Inactive' ?></td>
            <td>
                <?php if ($account['is_active']): ?>
                    <form method="post" action="index.php?route=users/deactivate" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($account['id']) ?>">
                        <button>Deactivate</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

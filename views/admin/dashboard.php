<h2>Admin Dashboard</h2>
<section class="grid">
    <div class="card">
        <h3>Enrollment by Class</h3>
        <?php foreach ($counts as $row): ?>
            <p><?= e($row['name'] . ' ' . $row['stream']) ?>: <strong><?= e($row['total']) ?></strong></p>
        <?php endforeach; ?>
    </div>
    <div class="card">
        <h3>Fee Summary</h3>
        <p>Due: <?= money($finance['due_total']) ?></p>
        <p>Paid: <?= money($finance['paid_total']) ?></p>
        <p>Outstanding: <?= money($finance['balance_total']) ?></p>
    </div>
    <div class="card">
        <h3>Today's Attendance</h3>
        <?php foreach ($attendance as $row): ?>
            <p><?= e(ucfirst($row['status'])) ?>: <?= e($row['total']) ?></p>
        <?php endforeach; ?>
    </div>
    <div class="card">
        <h3>Operations</h3>
        <p>Use the navigation to manage admissions, users, attendance, marks, invoices, payments, and reports.</p>
    </div>
</section>

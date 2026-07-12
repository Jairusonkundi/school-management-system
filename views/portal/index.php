<h2>My Records</h2>

<section class="grid">
    <?php foreach ($students as $student): ?>
        <div class="card">
            <h3><?= e($student['name']) ?></h3>
            <p><?= e($student['admission_no']) ?></p>
            <p><?= e($student['class_name'] . ' ' . $student['stream']) ?></p>
        </div>
    <?php endforeach; ?>
</section>

<h3>Attendance</h3>
<table>
    <thead><tr><th>Date</th><th>Status</th><th>Note</th></tr></thead>
    <tbody>
    <?php foreach ($attendance as $row): ?>
        <tr><td><?= e($row['date']) ?></td><td><?= e(ucfirst($row['status'])) ?></td><td><?= e($row['note']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Results</h3>
<table>
    <thead><tr><th>Term</th><th>Subject</th><th>Marks</th><th>CBC Level</th><th>Class Avg</th><th>Rank</th></tr></thead>
    <tbody>
    <?php foreach ($results as $row): ?>
        <tr>
            <td><?= e($row['term']) ?></td>
            <td><?= e($row['subject_name']) ?></td>
            <td><?= e($row['marks']) ?></td>
            <td><?= e(cbc_level_label($row['grade'])) ?></td>
            <td><?= number_format((float)$row['class_average'], 2) ?></td>
            <td><?= e($row['class_rank']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Fee Statement</h3>
<table>
    <thead><tr><th>Term</th><th>Amount Due</th><th>Paid</th><th>Balance</th></tr></thead>
    <tbody>
    <?php foreach ($invoices as $invoice): ?>
        <tr>
            <td><?= e($invoice['term']) ?></td>
            <td><?= money($invoice['amount_due']) ?></td>
            <td><?= money((float)$invoice['amount_due'] - (float)$invoice['balance']) ?></td>
            <td><?= money($invoice['balance']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

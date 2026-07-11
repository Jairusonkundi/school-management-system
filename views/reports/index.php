<h2>Management Reports</h2>

<h3>Enrollment Summary</h3>
<table>
    <thead><tr><th>Class</th><th>Students</th></tr></thead>
    <tbody>
    <?php foreach ($enrollment as $row): ?>
        <tr><td><?= e($row['name'] . ' ' . $row['stream']) ?></td><td><?= e($row['total']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Attendance Summary</h3>
<table>
    <thead><tr><th>Class</th><th>Present</th><th>Absent</th><th>Excused</th></tr></thead>
    <tbody>
    <?php foreach ($attendance as $row): ?>
        <tr><td><?= e($row['name'] . ' ' . $row['stream']) ?></td><td><?= e($row['present_count']) ?></td><td><?= e($row['absent_count']) ?></td><td><?= e($row['excused_count']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Academic Performance</h3>
<table>
    <thead><tr><th>Term</th><th>Class</th><th>Subject</th><th>Average</th><th>Highest</th><th>Lowest</th></tr></thead>
    <tbody>
    <?php foreach ($performance as $row): ?>
        <tr><td><?= e($row['term']) ?></td><td><?= e($row['name'] . ' ' . $row['stream']) ?></td><td><?= e($row['subject_name']) ?></td><td><?= number_format((float)$row['average_marks'], 2) ?></td><td><?= e($row['highest_marks']) ?></td><td><?= e($row['lowest_marks']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Fee Collection</h3>
<table>
    <thead><tr><th>Term</th><th>Invoices</th><th>Amount Due</th><th>Paid</th><th>Balance</th></tr></thead>
    <tbody>
    <?php foreach ($finance as $row): ?>
        <tr><td><?= e($row['term']) ?></td><td><?= e($row['invoice_count']) ?></td><td><?= money($row['amount_due']) ?></td><td><?= money($row['paid']) ?></td><td><?= money($row['balance']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>

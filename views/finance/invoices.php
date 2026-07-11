<h2>Fee Invoices</h2>
<form class="card form-grid" method="post">
    <?= csrf_field() ?>
    <label>Student
        <select name="student_id" required>
            <?php foreach ($lookups->students() as $s): ?>
                <option value="<?= e($s['id']) ?>"><?= e($s['admission_no'] . ' ' . $s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Term<input name="term" placeholder="2026 Term 1" required></label>
    <label>Amount due<input type="number" step="0.01" min="1" name="amount_due" required></label>
    <button>Generate Invoice</button>
</form>

<table>
    <thead><tr><th>Student</th><th>Term</th><th>Amount Due</th><th>Balance</th><th>Created</th></tr></thead>
    <tbody>
    <?php foreach ($invoices as $invoice): ?>
        <tr>
            <td><?= e($invoice['admission_no'] . ' ' . $invoice['student_name']) ?></td>
            <td><?= e($invoice['term']) ?></td>
            <td><?= money($invoice['amount_due']) ?></td>
            <td><?= money($invoice['balance']) ?></td>
            <td><?= e($invoice['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

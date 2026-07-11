<h2>Fee Payments</h2>
<?php if (empty($invoices)): ?>
    <p class="alert">There are no invoices with an outstanding balance.</p>
<?php endif; ?>
<form class="card form-grid" method="post">
    <?= csrf_field() ?>
    <label>Invoice
        <select name="invoice_id" required>
            <?php foreach ($invoices as $invoice): ?>
                <option value="<?= e($invoice['id']) ?>"><?= e($invoice['admission_no'] . ' ' . $invoice['student_name'] . ' - ' . $invoice['term'] . ' Balance ' . money($invoice['balance'])) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Amount<input type="number" step="0.01" min="1" name="amount" required></label>
    <label>Date<input type="date" name="date" value="<?= date('Y-m-d') ?>" required></label>
    <label>Method
        <select name="method">
            <option value="mpesa">M-Pesa</option>
            <option value="cash">Cash</option>
            <option value="bank">Bank</option>
            <option value="cheque">Cheque</option>
        </select>
    </label>
    <label>Reference<input name="reference" required></label>
    <button <?= empty($invoices) ? 'disabled' : '' ?>>Record Payment</button>
</form>

<table>
    <thead><tr><th>Invoice</th><th>Term</th><th>Amount Due</th><th>Balance</th></tr></thead>
    <tbody>
    <?php foreach ($allInvoices as $invoice): ?>
        <tr>
            <td><?= e($invoice['admission_no'] . ' ' . $invoice['student_name']) ?></td>
            <td><?= e($invoice['term']) ?></td>
            <td><?= money($invoice['amount_due']) ?></td>
            <td><?= money($invoice['balance']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

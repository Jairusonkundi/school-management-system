<?php
require_once __DIR__ . '/../../helpers/view_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_role('admin');
$pageTitle='Receipt';$id=(int)($_GET['payment_id']??0);$stmt=$pdo->prepare('SELECT p.*,fi.term,fi.academic_year,fi.balance,s.full_name,s.admission_no,c.class_name FROM payments p JOIN fee_invoices fi ON fi.invoice_id=p.invoice_id JOIN students s ON s.student_id=fi.student_id JOIN classes c ON c.class_id=s.class_id WHERE p.payment_id=?');$stmt->execute([$id]);$r=$stmt->fetch();require __DIR__ . '/../../includes/header.php';
?>
<?php if($r): ?><div class="card"><div class="card-body"><div class="text-center"><h2>Sunshine Kaseveni Academy</h2><h3 class="h5">Official Receipt</h3></div><table class="table"><tr><th>Receipt No</th><td><?= h($r['receipt_no']) ?></td></tr><tr><th>Date</th><td><?= h($r['payment_date']) ?></td></tr><tr><th>Student</th><td><?= h($r['full_name'].' ('.$r['admission_no'].')') ?></td></tr><tr><th>Class</th><td><?= h($r['class_name']) ?></td></tr><tr><th>Term / Year</th><td><?= h($r['term'].' / '.$r['academic_year']) ?></td></tr><tr><th>Amount Paid</th><td><?= money_fmt($r['amount_paid']) ?></td></tr><tr><th>Method</th><td><?= h($r['payment_method']) ?></td></tr><tr><th>Balance After Payment</th><td><?= money_fmt($r['balance']) ?></td></tr><tr><th>Received By</th><td>System</td></tr></table><button class="btn btn-primary no-print" onclick="print()">Print</button> <a class="btn btn-secondary no-print" href="payment.php">Back</a></div></div><?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

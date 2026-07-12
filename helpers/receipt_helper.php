<?php
function generate_receipt_no(PDO $pdo, int $term, string $year): string
{
    $last_id = $pdo->lastInsertId();
    return "SKA-{$year}-T{$term}-" . str_pad((string)$last_id, 5, '0', STR_PAD_LEFT);
}

<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function send_notification(
    PDO $pdo,
    int $recipient_id,
    string $to_email,
    string $to_name,
    string $subject,
    string $message,
    string $type
): bool {
    $allowed = ['Absence', 'Payment', 'Fee Arrears', 'Announcement', 'Disciplinary'];
    if (!in_array($type, $allowed, true)) {
        return false;
    }

    global $mail_host, $mail_port, $mail_encrypt, $mail_user, $mail_pass, $mail_from, $mail_from_name;
    $sent = false;

    try {
        if (filter_var($to_email, FILTER_VALIDATE_EMAIL) && $mail_user !== 'your-email@gmail.com') {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $mail_host;
            $mail->SMTPAuth = true;
            $mail->Username = $mail_user;
            $mail->Password = $mail_pass;
            $mail->SMTPSecure = $mail_encrypt;
            $mail->Port = $mail_port;
            $mail->setFrom($mail_from, $mail_from_name);
            $mail->addAddress($to_email, $to_name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
            $mail->AltBody = $message;
            $sent = $mail->send();
        }
    } catch (Exception $e) {
        error_log('SKA mail failed: ' . $e->getMessage());
        $sent = false;
    }

    $stmt = $pdo->prepare('INSERT INTO notifications (recipient_id, subject, message, type, sent_at, status) VALUES (?, ?, ?, ?, NOW(), ?)');
    $stmt->execute([$recipient_id, $subject, $message, $type, $sent ? 'Sent' : 'Failed']);
    return $sent;
}

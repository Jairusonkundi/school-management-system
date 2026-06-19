<?php
namespace App\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationService
{
    public function queue(int $userId, string $subject, string $message): void
    {
        $stmt = Database::getConnection()->prepare('INSERT INTO notifications (user_id, subject, message) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $subject, $message]);
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        $config = require __DIR__ . '/../../config/config.php';
        $mailConfig = $config['mail'];
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $mailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mailConfig['username'];
            $mail->Password = $mailConfig['password'];
            $mail->SMTPSecure = $mailConfig['encryption'];
            $mail->Port = $mailConfig['port'];
            $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
            return $mail->send();
        } catch (Exception $e) {
            error_log('Mail failed: ' . $mail->ErrorInfo);
            return false;
        }
    }
}

<?php
namespace App\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class NotificationService
{
    public function sendToUser(int $userId, string $email, string $type, string $message): bool
    {
        if ($userId < 1 || !in_array($type, ['payment_receipt', 'absence_alert', 'system'], true)) {
            return false;
        }
        $sent = $this->sendEmail($email, $this->subjectFor($type), $message);
        $stmt = Database::getConnection()->prepare('INSERT INTO notifications (recipient_user_id, type, message, sent_at, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $type, $message, $sent ? date('Y-m-d H:i:s') : null, $sent ? 'sent' : 'failed']);
        return $sent;
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
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
            $mail->AltBody = $body;
            return $mail->send();
        } catch (Exception $e) {
            error_log('Mail failed: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function subjectFor(string $type): string
    {
        return match ($type) {
            'payment_receipt' => 'Sunshine Kaseveni Academy payment receipt',
            'absence_alert' => 'Sunshine Kaseveni Academy absence alert',
            default => 'Sunshine Kaseveni Academy notification',
        };
    }
}

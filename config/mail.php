<?php
use PHPMailer\PHPMailer\PHPMailer;

$mail_host = 'smtp.gmail.com';
$mail_port = 587;
$mail_encrypt = PHPMailer::ENCRYPTION_STARTTLS;
$mail_user = 'your-email@gmail.com';
$mail_pass = 'your-app-password-here';
$mail_from = 'noreply@ska.ac.ke';
$mail_from_name = 'Sunshine Kaseveni Academy';

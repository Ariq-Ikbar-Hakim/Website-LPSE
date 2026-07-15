<?php
require 'C:\xampp\htdocs\LPSE\PHPMailer-PHPMailer-36d2461\src\Exception.php';
require 'C:\xampp\htdocs\LPSE\PHPMailer-PHPMailer-36d2461\src\PHPMailer.php';
require 'C:\xampp\htdocs\LPSE\PHPMailer-PHPMailer-36d2461\src\SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ariq20055@gmail.com';
    $mail->Password   = 'qpjy ibna lvvt jinc';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->setFrom('ariq20055@gmail.com', 'LPSE APELBAJA');
    $mail->addAddress('ariq20055@gmail.com', 'Test User');
    $mail->isHTML(true);
    $mail->Subject = 'Test Email LPSE APELBAJA';
    $mail->Body    = '<h3>Halo!</h3><p>Ini adalah email percobaan dari sistem LPSE APELBAJA.</p>';
    $mail->send();
    echo "\n\n=== SUCCESS: Email berhasil dikirim! ===\n";
} catch (Exception $e) {
    echo "\n\n=== ERROR: " . $mail->ErrorInfo . " ===\n";
}

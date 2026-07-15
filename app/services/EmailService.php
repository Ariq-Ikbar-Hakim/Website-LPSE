<?php
/**
 * Service Email — menangani pengiriman email (Brevo SMTP / PHPMailer)
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer library files manually (User has downloaded them)
$phpmailer_dir = BASEPATH . '/PHPMailer-PHPMailer-36d2461/src/';
if (file_exists($phpmailer_dir . 'Exception.php')) {
    require_once $phpmailer_dir . 'Exception.php';
    require_once $phpmailer_dir . 'PHPMailer.php';
    require_once $phpmailer_dir . 'SMTP.php';
}

class EmailService
{
    public function sendResetPasswordEmail(string $toEmail, string $toName, string $token): bool
    {
        $resetUrl = APP_URL . '/index.php?page=reset_password&token=' . urlencode($token);

        $subject = 'Reset Password - LPSE APELBAJA';
        
        // HTML Message
        $messageHTML = "<h3>Halo {$toName},</h3>";
        $messageHTML .= "<p>Admin telah menyetujui permintaan reset password Anda.</p>";
        $messageHTML .= "<p>Silakan klik tombol di bawah ini untuk mereset password:</p>";
        $messageHTML .= "<p><a href='{$resetUrl}' style='display:inline-block;padding:10px 20px;background-color:#2563eb;color:#fff;text-decoration:none;border-radius:5px;'>Reset Password</a></p>";
        $messageHTML .= "<p>Atau copy link berikut: <br><a href='{$resetUrl}'>{$resetUrl}</a></p>";
        $messageHTML .= "<p>Link ini valid selama " . RESET_TOKEN_EXPIRY_HOURS . " jam.</p>";
        $messageHTML .= "<br><p>Salam,<br>Tim LPSE APELBAJA</p>";

        // Plain text fallback
        $messagePlain = "Halo {$toName},\n\n";
        $messagePlain .= "Admin telah menyetujui permintaan reset password Anda.\n";
        $messagePlain .= "Silakan buka link berikut untuk mereset password:\n";
        $messagePlain .= $resetUrl . "\n\n";
        $messagePlain .= "Link ini valid selama " . RESET_TOKEN_EXPIRY_HOURS . " jam.\n";
        $messagePlain .= "Salam,\nTim LPSE APELBAJA";

        // Jika PHPMailer ada
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_HOST : SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_USER : SMTP_USER;
                $mail->Password   = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_PASS : SMTP_PASS;
                $mail->SMTPSecure = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_SECURE : SMTP_SECURE;
                $mail->Port       = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_PORT : SMTP_PORT;

                // Disable debug output unless testing
                $mail->SMTPDebug = SMTP::DEBUG_OFF;

                // Recipients
                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress($toEmail, $toName);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $messageHTML;
                $mail->AltBody = $messagePlain;

                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                return false;
            }
        } else {
            // Fallback ke fungsi mail() native jika class tidak ditemukan
            $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_ADDRESS . ">\r\n";
            $headers .= "Reply-To: " . MAIL_FROM_ADDRESS . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            return @mail($toEmail, $subject, $messageHTML, $headers);
        }
    }

    public function sendVerificationEmail(string $toEmail, string $toName, string $jabatan): bool
    {
        $loginUrl = APP_URL . '/index.php?page=login';
        $subject = 'Akun Anda Telah Dikonfirmasi - LPSE APELBAJA';
        
        $messageHTML = "<h3>Halo {$toName},</h3>";
        $messageHTML .= "<p>Pendaftaran akun Anda sebagai <b>{$jabatan}</b> telah disetujui oleh Administrator.</p>";
        $messageHTML .= "<p>Anda sekarang sudah bisa masuk ke dalam sistem menggunakan email dan password yang telah didaftarkan.</p>";
        $messageHTML .= "<p><a href='{$loginUrl}' style='display:inline-block;padding:10px 20px;background-color:#059669;color:#fff;text-decoration:none;border-radius:5px;'>Login Sekarang</a></p>";
        $messageHTML .= "<br><p>Salam,<br>Tim LPSE APELBAJA</p>";

        $messagePlain = "Halo {$toName},\n\n";
        $messagePlain .= "Pendaftaran akun Anda sebagai {$jabatan} telah disetujui oleh Administrator.\n";
        $messagePlain .= "Anda sekarang sudah bisa masuk ke dalam sistem menggunakan email dan password yang telah didaftarkan.\n\n";
        $messagePlain .= "Login di sini: {$loginUrl}\n\n";
        $messagePlain .= "Salam,\nTim LPSE APELBAJA";

        return $this->sendActualEmail($toEmail, $toName, $subject, $messageHTML, $messagePlain);
    }

    public function sendRejectionEmail(string $toEmail, string $toName, string $jabatan): bool
    {
        $subject = 'Status Pendaftaran Akun - LPSE APELBAJA';
        
        $messageHTML = "<h3>Halo {$toName},</h3>";
        $messageHTML .= "<p>Mohon maaf, pendaftaran akun Anda sebagai <b>{$jabatan}</b> belum dapat disetujui (dibatalkan) oleh Administrator.</p>";
        $messageHTML .= "<p>Hal ini mungkin dikarenakan adanya kesalahan atau ketidaksesuaian pada data Anda. Silakan periksa kembali data Anda dan lakukan pendaftaran ulang dengan data yang benar, atau hubungi pihak Administrator untuk informasi lebih lanjut.</p>";
        $messageHTML .= "<br><p>Salam,<br>Tim LPSE APELBAJA</p>";

        $messagePlain = "Halo {$toName},\n\n";
        $messagePlain .= "Mohon maaf, pendaftaran akun Anda sebagai {$jabatan} belum dapat disetujui (dibatalkan) oleh Administrator.\n";
        $messagePlain .= "Hal ini mungkin dikarenakan adanya kesalahan atau ketidaksesuaian pada data Anda. Silakan periksa kembali data Anda dan lakukan pendaftaran ulang dengan data yang benar, atau hubungi pihak Administrator untuk informasi lebih lanjut.\n\n";
        $messagePlain .= "Salam,\nTim LPSE APELBAJA";

        return $this->sendActualEmail($toEmail, $toName, $subject, $messageHTML, $messagePlain);
    }

    private function sendActualEmail(string $toEmail, string $toName, string $subject, string $messageHTML, string $messagePlain): bool
    {
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_HOST : SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_USER : SMTP_USER;
                $mail->Password   = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_PASS : SMTP_PASS;
                $mail->SMTPSecure = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_SECURE : SMTP_SECURE;
                $mail->Port       = MAIL_DRIVER === 'brevo' ? BREVO_SMTP_PORT : SMTP_PORT;

                $mail->SMTPDebug = SMTP::DEBUG_OFF;

                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress($toEmail, $toName);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $messageHTML;
                $mail->AltBody = $messagePlain;

                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                return false;
            }
        } else {
            $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_ADDRESS . ">\r\n";
            $headers .= "Reply-To: " . MAIL_FROM_ADDRESS . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            return @mail($toEmail, $subject, $messageHTML, $headers);
        }
    }

    public function sendTransferNotificationToTarget(string $toEmail, string $toName, string $namaPaket, string $roleBaru): bool
    {
        $subject = 'Pemberitahuan Limpahan Paket - LPSE APELBAJA';
        
        $messageHTML = "<h3>Halo {$toName},</h3>";
        $messageHTML .= "<p>Anda telah ditunjuk sebagai <b>{$roleBaru}</b> untuk paket pekerjaan <b>{$namaPaket}</b> melalui proses Pengajuan Transfer Paket yang disetujui oleh Admin.</p>";
        $messageHTML .= "<p>Silakan login ke aplikasi APELBAJA untuk melihat paket tersebut di dashboard Anda.</p>";
        $messageHTML .= "<br><p>Salam,<br>Tim LPSE APELBAJA</p>";

        $messagePlain = "Halo {$toName},\n\n";
        $messagePlain .= "Anda telah ditunjuk sebagai {$roleBaru} untuk paket pekerjaan {$namaPaket} melalui proses Pengajuan Transfer Paket yang disetujui oleh Admin.\n";
        $messagePlain .= "Silakan login ke aplikasi APELBAJA untuk melihat paket tersebut di dashboard Anda.\n\n";
        $messagePlain .= "Salam,\nTim LPSE APELBAJA";

        return $this->sendActualEmail($toEmail, $toName, $subject, $messageHTML, $messagePlain);
    }

    public function sendTransferNotificationToSender(string $toEmail, string $toName, string $namaPaket, string $status, string $catatan = ''): bool
    {
        $subject = 'Status Pengajuan Transfer Paket - LPSE APELBAJA';
        
        $statusText = strtoupper($status);
        $messageHTML = "<h3>Halo {$toName},</h3>";
        $messageHTML .= "<p>Pengajuan transfer Anda untuk paket <b>{$namaPaket}</b> telah <b>{$statusText}</b> oleh Administrator.</p>";
        
        if ($status === 'ditolak' && $catatan !== '') {
            $messageHTML .= "<p>Catatan Admin: <i>{$catatan}</i></p>";
        }
        
        $messageHTML .= "<p>Silakan login ke aplikasi APELBAJA untuk melihat detail riwayat pengajuan Anda.</p>";
        $messageHTML .= "<br><p>Salam,<br>Tim LPSE APELBAJA</p>";

        $messagePlain = "Halo {$toName},\n\n";
        $messagePlain .= "Pengajuan transfer Anda untuk paket {$namaPaket} telah {$statusText} oleh Administrator.\n";
        
        if ($status === 'ditolak' && $catatan !== '') {
            $messagePlain .= "Catatan Admin: {$catatan}\n";
        }
        
        $messagePlain .= "Silakan login ke aplikasi APELBAJA untuk melihat detail riwayat pengajuan Anda.\n\n";
        $messagePlain .= "Salam,\nTim LPSE APELBAJA";

        return $this->sendActualEmail($toEmail, $toName, $subject, $messageHTML, $messagePlain);
    }
}

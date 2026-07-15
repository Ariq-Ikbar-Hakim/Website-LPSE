<?php
/**
 * AdminController — semua fungsi monitoring & verifikasi admin
 */
class AdminController
{
    private User $userModel;
    private AuditLog $auditModel;

    public function __construct(mysqli $db)
    {
        $this->userModel = new User($db);
        $this->auditModel = new AuditLog($db);
    }

    public function konfirmasiAkun(): void
    {
        RoleMiddleware::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $userId = (int)$_POST['user_id'];
            $action = $_POST['action'];

            if ($action === 'terima') {
                $this->userModel->setActive($userId, 1);
                
                $user = $this->userModel->findById($userId);
                if ($user) {
                    require_once BASEPATH . '/app/services/EmailService.php';
                    $emailService = new EmailService();
                    $emailSent = $emailService->sendVerificationEmail($user['email'], $user['nama'], $user['jabatan_aktif']);
                    if ($emailSent) {
                        flashSet('success', 'Akun berhasil diverifikasi dan email notifikasi telah dikirim ke pengguna.');
                    } else {
                        flashSet('success', 'Akun berhasil diverifikasi, namun email notifikasi gagal dikirim (Cek konfigurasi SMTP).');
                    }
                } else {
                    flashSet('success', 'Akun berhasil diverifikasi.');
                }
            } elseif ($action === 'tolak') {
                $user = $this->userModel->findById($userId);
                if ($user) {
                    require_once BASEPATH . '/app/services/EmailService.php';
                    $emailService = new EmailService();
                    $emailSent = $emailService->sendRejectionEmail($user['email'], $user['nama'], $user['jabatan_aktif']);
                    
                    // Hapus user yang ditolak dari database
                    global $conn;
                    $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
                    $stmt->bind_param('i', $userId);
                    $stmt->execute();
                    $stmt->close();

                    if ($emailSent) {
                        flashSet('success', 'Pendaftaran akun berhasil dibatalkan/ditolak dan email pemberitahuan telah dikirim.');
                    } else {
                        flashSet('success', 'Pendaftaran akun dibatalkan/ditolak, namun email pemberitahuan gagal dikirim.');
                    }
                } else {
                    flashSet('error', 'Pengguna tidak ditemukan.');
                }
            }
            redirect('index.php?page=admin_konfirmasi');
        }

        $users = $this->userModel->getAll('menunggu');
        require BASEPATH . '/views/admin/konfirmasi_akun.php';
    }

    public function monitoringUsulan(): void
    {
        RoleMiddleware::requireRole('admin');
        
        global $conn;
        $paketModel = new Paket($conn);
        $pakets = $paketModel->getAll();
        
        require BASEPATH . '/views/admin/monitoring_usulan.php';
    }

    public function resetPasswordRequests(): void
    {
        RoleMiddleware::requireRole('admin');

        global $conn;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $reqId = (int)$_POST['request_id'];
            
            // Logic approve & send email
            $stmt = $conn->prepare("SELECT r.*, u.email, u.nama FROM password_reset_requests r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
            $stmt->bind_param('i', $reqId);
            $stmt->execute();
            $req = $stmt->get_result()->fetch_assoc();

            if ($req && $req['status'] === 'menunggu') {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $this->userModel->saveResetToken($req['user_id'], $token, $expires);
                
                $upd = $conn->prepare("UPDATE password_reset_requests SET status = 'disetujui', token = ?, disetujui_oleh = ?, disetujui_at = NOW(), expires_at = ? WHERE id = ?");
                $adminId = $_SESSION['user_id'];
                $upd->bind_param('sisi', $token, $adminId, $expires, $reqId);
                $upd->execute();

                $emailService = new EmailService();
                $isEmailSent = $emailService->sendResetPasswordEmail($req['email'], $req['nama'], $token);

                $resetUrl = APP_URL . '/index.php?page=reset_password&token=' . urlencode($token);
                
                if ($isEmailSent) {
                    flashSet('success', "Reset password disetujui. Email token telah dikirim ke user.<br>Jika user tidak menerima email, copy link ini: <a href='{$resetUrl}' target='_blank' class='underline font-bold'>{$resetUrl}</a>");
                } else {
                    flashSet('error', "Gagal mengirim email (Cek konfigurasi config/mail.php).<br>Namun token berhasil dibuat. Berikan link ini manual ke user: <a href='{$resetUrl}' target='_blank' class='underline font-bold'>{$resetUrl}</a>");
                }
            }
            redirect('index.php?page=admin_reset_password');
        }

        $stmt = $conn->query("SELECT r.*, u.nama, u.nip, u.jabatan_aktif, u.no_telp FROM password_reset_requests r JOIN users u ON r.user_id = u.id ORDER BY r.diminta_at DESC");
        $requests = $stmt->fetch_all(MYSQLI_ASSOC);

        require BASEPATH . '/views/admin/reset_password.php';
    }
}

<?php
/**
 * Service Auth — menangani login, register, session, dll.
 */
class AuthService
{
    private User $userModel;
    private AuditService $auditService;

    public function __construct(mysqli $db)
    {
        $this->userModel = new User($db);
        $this->auditService = new AuditService($db);
    }

    public function login(string $nip, string $password): array
    {
        $user = $this->userModel->findByNip($nip);

        if (!$user) {
            return ['success' => false, 'message' => 'NIP atau Password salah!'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'NIP atau Password salah!'];
        }

        if ((int)$user['status_aktif'] !== 1) {
            return ['success' => false, 'message' => 'Akun belum diverifikasi admin. Hubungi admin.'];
        }

        // Set session
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['nip'] = $user['nip'];
        $_SESSION['jabatan_aktif'] = $user['jabatan_aktif'];

        // Update last login
        $this->userModel->updateLastLogin((int)$user['id']);

        // Log
        $this->auditService->log('LOGIN', 'users', (int)$user['id'], null, null, 'User berhasil login');

        return ['success' => true];
    }

    public function register(array $data): array
    {
        if ($this->userModel->nipExists($data['nip'])) {
            return ['success' => false, 'message' => 'NIP sudah terdaftar.'];
        }

        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email sudah terdaftar.'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $id = $this->userModel->create($data);
        if ($id) {
            $this->auditService->log('CREATE', 'users', $id, null, $data, 'Pendaftaran akun baru');
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan data ke database.'];
    }

    public function requestPasswordReset(string $nip): array
    {
        $user = $this->userModel->findByNip($nip);
        if (!$user) {
            return ['success' => false, 'message' => 'NIP tidak ditemukan.'];
        }

        // Insert to password_reset_requests logic should be in a model, doing a simple approach
        global $conn;
        $stmt = $conn->prepare("INSERT INTO password_reset_requests (user_id, diminta_at) VALUES (?, NOW())");
        $stmt->bind_param("i", $user['id']);
        if ($stmt->execute()) {
             $this->auditService->log('RESET_PASSWORD', 'users', $user['id'], null, null, 'Meminta reset password');
             return ['success' => true];
        }
        return ['success' => false, 'message' => 'Terjadi kesalahan sistem.'];
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->auditService->log('LOGOUT', 'users', $_SESSION['user_id'], null, null, 'User logout');
        }
        session_unset();
        session_destroy();
    }

    public function verifyResetToken(string $token): bool
    {
        global $conn;
        $stmt = $conn->prepare("SELECT id FROM password_reset_requests WHERE token = ? AND status = 'disetujui' AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function resetPasswordWithToken(string $token, string $newPassword): array
    {
        global $conn;
        
        // 1. Dapatkan user_id dari token
        $stmt = $conn->prepare("SELECT id, user_id FROM password_reset_requests WHERE token = ? AND status = 'disetujui' AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Token tidak valid atau sudah kedaluwarsa.'];
        }
        
        $row = $result->fetch_assoc();
        $requestId = $row['id'];
        $userId = $row['user_id'];
        
        $conn->begin_transaction();
        try {
            // 2. Hash password baru dan update ke users
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmtUpdateUser = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmtUpdateUser->bind_param("si", $hash, $userId);
            $stmtUpdateUser->execute();
            
            // 3. Update status request
            $stmtUpdateReq = $conn->prepare("UPDATE password_reset_requests SET status = 'digunakan', digunakan_at = NOW() WHERE id = ?");
            $stmtUpdateReq->bind_param("i", $requestId);
            $stmtUpdateReq->execute();
            
            // 4. Audit Log
            $this->auditService->log('PASSWORD_CHANGED', 'users', $userId, null, null, 'Password direset menggunakan token email');
            
            $conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Gagal mereset password: ' . $e->getMessage()];
        }
    }
}

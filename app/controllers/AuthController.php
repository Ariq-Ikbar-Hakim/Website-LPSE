<?php
/**
 * AuthController — menangani request login, register, logout, reset password
 */
class AuthController
{
    private AuthService $authService;
    private EmailService $emailService;

    public function __construct(mysqli $db)
    {
        $this->authService = new AuthService($db);
        $this->emailService = new EmailService();
    }

    public function login(): void
    {
        AuthMiddleware::requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $v3_token = $_POST['recaptcha_v3_token'] ?? '';
            $v2_token = $_POST['g-recaptcha-response'] ?? '';
            $ip       = $_SERVER['REMOTE_ADDR'];

            // ── Validasi v3 ──────────────────────────────────────────────
            if (empty($v3_token)) {
                flashSet('error', 'Token reCAPTCHA v3 tidak ditemukan. Silakan muat ulang halaman.');
                redirect('index.php?page=login');
            } else {
                $v3 = ValidationHelper::verifyCaptcha(RECAPTCHA_V3_SECRET_KEY, $v3_token, $ip);

                if (!empty($v3['_connection_error'])) {
                    flashSet('error', 'Tidak dapat menghubungi server reCAPTCHA. Silakan coba lagi.');
                    redirect('index.php?page=login');
                } elseif (
                    empty($v3['success']) ||
                    !isset($v3['action']) || $v3['action'] !== 'login' ||
                    !isset($v3['score'])  || $v3['score']  <  RECAPTCHA_V3_MIN_SCORE
                ) {
                    flashSet('error', 'Verifikasi otomatis gagal. Silakan coba lagi.');
                    redirect('index.php?page=login');
                }
            }

            // ── Validasi v2 ──────────────────────────────────────────────
            if (empty($v2_token)) {
                flashSet('error', 'Centang "Saya bukan robot" terlebih dahulu.');
                redirect('index.php?page=login');
            } else {
                $v2 = ValidationHelper::verifyCaptcha(RECAPTCHA_V2_SECRET_KEY, $v2_token, $ip);

                if (!empty($v2['_connection_error'])) {
                    flashSet('error', 'Tidak dapat menghubungi server reCAPTCHA. Silakan coba lagi.');
                    redirect('index.php?page=login');
                } elseif (empty($v2['success'])) {
                    flashSet('error', 'Verifikasi checkbox reCAPTCHA gagal. Silakan centang ulang.');
                    redirect('index.php?page=login');
                }
            }
            
            $nip = trim($_POST['nip'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($nip) || empty($password)) {
                flashSet('error', 'NIP dan Password wajib diisi.');
                redirect('index.php?page=login');
            }

            $result = $this->authService->login($nip, $password);

            if ($result['success']) {
                redirect('index.php');
            } else {
                flashSet('error', $result['message']);
                redirect('index.php?page=login');
            }
        }

        require BASEPATH . '/views/auth/login.php';
    }

    public function register(): void
    {
        AuthMiddleware::requireGuest();

        $errors = [];
        $captcha_error = '';
        $success_msg = '';
        $old = ['nip' => '', 'nama' => '', 'email' => '', 'no_telp' => '', 'opd' => '', 'jabatan_aktif' => 'PPK'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nip' => trim($_POST['nip'] ?? ''),
                'nama' => trim($_POST['nama'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'no_telp' => trim($_POST['no_telp'] ?? ''),
                'opd' => trim($_POST['opd'] ?? ''),
                'sub_unit_opd' => trim($_POST['sub_unit_opd'] ?? ''),
                'jabatan_aktif' => $_POST['jabatan_aktif'] ?? 'PPK',
                'sk_nomor' => trim($_POST['sk_nomor'] ?? ''),
                'sk_mulai' => empty($_POST['sk_mulai']) ? null : $_POST['sk_mulai'],
                'sk_sampai' => empty($_POST['sk_sampai']) ? null : $_POST['sk_sampai'],
                'keterangan' => trim($_POST['keterangan'] ?? '')
            ];
            
            $old = array_merge($old, $data);

            // reCAPTCHA v3
            $v3_token = $_POST['recaptcha_v3_token'] ?? '';
            $ip       = $_SERVER['REMOTE_ADDR'];
            
            if (empty($v3_token)) {
                $captcha_error = 'Token reCAPTCHA v3 tidak ditemukan. Silakan muat ulang halaman.';
            } else {
                $v3 = ValidationHelper::verifyCaptcha(RECAPTCHA_V3_SECRET_KEY, $v3_token, $ip);
                if (!empty($v3['_connection_error'])) {
                    $captcha_error = 'Tidak dapat menghubungi server reCAPTCHA. Silakan coba lagi.';
                } elseif (
                    empty($v3['success']) ||
                    !isset($v3['action']) || $v3['action'] !== 'register' ||
                    !isset($v3['score'])  || $v3['score']  <  RECAPTCHA_V3_MIN_SCORE
                ) {
                    $captcha_error = 'Verifikasi otomatis gagal. Kemungkinan aktivitas bot terdeteksi.';
                }
            }

            if (empty($captcha_error)) {
                $errors_list = ValidationHelper::required($data, ['nip', 'nama', 'email', 'password', 'opd', 'jabatan_aktif', 'no_telp']);
                foreach ($errors_list as $err) {
                    // Quick map back to field
                    if (strpos($err, 'Nip') !== false) $errors['nip'] = $err;
                    elseif (strpos($err, 'Nama') !== false) $errors['nama'] = $err;
                    elseif (strpos($err, 'Email') !== false) $errors['email'] = $err;
                    elseif (strpos($err, 'Password') !== false) $errors['password'] = $err;
                    elseif (strpos($err, 'Opd') !== false) $errors['opd'] = $err;
                    elseif (strpos($err, 'Jabatan') !== false) $errors['jabatan_aktif'] = $err;
                    elseif (strpos($err, 'No telp') !== false) $errors['no_telp'] = $err;
                    else $errors['general'] = $err;
                }
                
                if (!ValidationHelper::email($data['email'])) {
                    $errors['email'] = 'Format email tidak valid.';
                }

                if (empty($errors)) {
                    $result = $this->authService->register($data);
                    if ($result['success']) {
                        $success_msg = 'Registrasi berhasil! Akun Anda sedang diproses oleh Administrator. Silakan periksa kotak masuk email (terletak pada folder spam bukan di inbox) yang Anda daftarkan secara berkala untuk informasi persetujuan akun.';
                        $old = []; // clear form
                    } else {
                        // Could be nip or email exists
                        if (strpos(strtolower($result['message']), 'nip') !== false) {
                            $errors['nip'] = $result['message'];
                        } elseif (strpos(strtolower($result['message']), 'email') !== false) {
                            $errors['email'] = $result['message'];
                        } else {
                            $errors['general'] = $result['message'];
                        }
                    }
                }
            }
        }

        require BASEPATH . '/views/auth/register.php';
    }

    public function logout(): void
    {
        $this->authService->logout();
        redirect('index.php?page=login');
    }

    public function lupaPassword(): void
    {
        AuthMiddleware::requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nip = trim($_POST['nip'] ?? '');
            if (empty($nip)) {
                flashSet('error', 'NIP wajib diisi.');
            } else {
                $result = $this->authService->requestPasswordReset($nip);
                if ($result['success']) {
                    flashSet('success', 'Permintaan reset password telah dikirim. Hubungi admin untuk persetujuan via WhatsApp: 083830237808 atau 085867276889.');
                } else {
                    flashSet('error', $result['message']);
                }
            }
            redirect('index.php?page=lupa_password');
        }

        require BASEPATH . '/views/auth/lupa_password.php';
    }

    public function resetPasswordForm(): void
    {
        AuthMiddleware::requireGuest();
        
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            flashSet('error', 'Token reset password tidak valid.');
            redirect('index.php?page=login');
        }

        $isValid = $this->authService->verifyResetToken($token);
        if (!$isValid) {
            flashSet('error', 'Token reset password tidak valid atau sudah kedaluwarsa.');
            redirect('index.php?page=login');
        }

        require BASEPATH . '/views/auth/reset_password.php';
    }

    public function updatePassword(): void
    {
        AuthMiddleware::requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($token) || empty($password)) {
                flashSet('error', 'Semua field wajib diisi.');
                redirect("index.php?page=reset_password&token=" . urlencode($token));
            }

            if ($password !== $password_confirm) {
                flashSet('error', 'Konfirmasi password tidak cocok.');
                redirect("index.php?page=reset_password&token=" . urlencode($token));
            }

            if (strlen($password) < 6) {
                flashSet('error', 'Password minimal 6 karakter.');
                redirect("index.php?page=reset_password&token=" . urlencode($token));
            }

            $result = $this->authService->resetPasswordWithToken($token, $password);
            
            if ($result['success']) {
                flashSet('success', 'Password berhasil diubah. Silakan login dengan password baru.');
                redirect('index.php?page=login');
            } else {
                flashSet('error', $result['message']);
                redirect("index.php?page=reset_password&token=" . urlencode($token));
            }
        } else {
            redirect('index.php?page=login');
        }
    }
}

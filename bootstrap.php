<?php
/**
 * Bootstrap — titik masuk seluruh aplikasi LPSE APELBAJA
 *
 * Cara pakai di setiap halaman:
 *   require_once __DIR__ . '/bootstrap.php';
 */

// ── Definisikan BASEPATH ─────────────────────────────────────
define('BASEPATH', __DIR__);

// ── Load konfigurasi ─────────────────────────────────────────
if (file_exists(BASEPATH . '/vendor/autoload.php')) {
    require_once BASEPATH . '/vendor/autoload.php';
}
require_once BASEPATH . '/config/app.php';
require_once BASEPATH . '/config/database.php';
require_once BASEPATH . '/config/mail.php';

// ── Session secure ───────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// ── Autoload kelas dari app/ ─────────────────────────────────
spl_autoload_register(function (string $class): void {
    $dirs = [
        BASEPATH . '/app/helpers/',
        BASEPATH . '/app/middleware/',
        BASEPATH . '/app/models/',
        BASEPATH . '/app/services/',
        BASEPATH . '/app/controllers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Koneksi Database (MySQLi) ────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    http_response_code(503);
    error_log('[DB ERROR] ' . $conn->connect_error);
    die('<h3 style="font-family:sans-serif;color:#b91c1c;padding:2rem">
         Koneksi database gagal. Hubungi administrator sistem.</h3>');
}

$conn->set_charset(DB_CHARSET);
$conn->query("SET time_zone = '+07:00'");

// ── Helper global (backward-compatible) ─────────────────────
function isLogin(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getRole(): string
{
    return $_SESSION['jabatan_aktif'] ?? '';
}

function isRole(string ...$roles): bool
{
    return in_array(getRole(), $roles, true);
}

function redirect(string $url): never
{
    header('Location: ' . APP_URL . '/' . ltrim($url, '/'));
    exit;
}

function redirectRaw(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatRupiah(float $nominal): string
{
    return 'Rp ' . number_format($nominal, 0, ',', '.');
}

function formatTanggal(string $date, string $format = 'd M Y'): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('<h3 style="font-family:sans-serif;color:#b91c1c;padding:2rem">
             CSRF token tidak valid. Silakan muat ulang halaman.</h3>');
    }
}

function flashSet(string $key, string $msg): void
{
    $_SESSION['flash'][$key] = $msg;
}

function flashGet(string $key): string
{
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flashHas(string $key): bool
{
    return !empty($_SESSION['flash'][$key]);
}

function currentPage(): string
{
    return basename($_SERVER['PHP_SELF']);
}

function activeNav(string $page): string
{
    return currentPage() === $page
        ? 'bg-white text-slate-900 shadow-md font-semibold'
        : 'hover:bg-white/10 text-slate-300';
}

function activeNavIcon(string $page, string $activeClass, string $defaultClass): string
{
    return currentPage() === $page ? $activeClass : $defaultClass;
}

// ── Pengecekan Role Real-time (Force Logout jika di-swap) ──
if (isLogin()) {
    $currentUserId = (int)$_SESSION['user_id'];
    $sessionRole = $_SESSION['jabatan_aktif'] ?? '';
    
    $stmt_cek = $conn->prepare("SELECT jabatan_aktif FROM users WHERE id = ?");
    $stmt_cek->bind_param("i", $currentUserId);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result();
    
    if ($res_cek && $res_cek->num_rows > 0) {
        $dbUser = $res_cek->fetch_assoc();
        if ($dbUser['jabatan_aktif'] !== $sessionRole) {
            // Jabatan berubah (kemungkinan karena swap Transfer Paket)
            session_unset();
            session_destroy();
            
            // Buat session baru untuk menyimpan pesan flash
            session_start();
            flashSet('error', 'Jabatan / akun Anda telah diubah oleh sistem (Transfer Jabatan). Silakan login ulang untuk menyesuaikan hak akses.');
            
            // Redirect ke halaman login
            if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
                header('Location: login.php');
                exit;
            }
        }
    }
    $stmt_cek->close();
}

<?php
/**
 * Konfigurasi Aplikasi LPSE APELBAJA
 */

// ── Proteksi akses langsung ──────────────────────────────────
defined('BASEPATH') or die('No direct script access allowed');

// ── Informasi Aplikasi ───────────────────────────────────────
define('APP_NAME',    'LPSE APELBAJA');
define('APP_VERSION', '2.0.0');
define('APP_URL',     'http://localhost/LPSE');
define('APP_ENV',     'development'); // 'development' | 'production'

// ── Kontak Admin ─────────────────────────────────────────────
define('ADMIN_WA_1', '083830237808');
define('ADMIN_WA_2', '085867276889');

// ── Konfigurasi Upload ───────────────────────────────────────
define('UPLOAD_MAX_SIZE',    10 * 1024 * 1024); // 10 MB
define('UPLOAD_PATH_SK',     BASEPATH . '/uploads/sk/');
define('UPLOAD_PATH_LAMPIRAN', BASEPATH . '/uploads/lampiran/');
define('UPLOAD_PATH_QR',     BASEPATH . '/uploads/qr/');
define('UPLOAD_ALLOWED_EXT', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);

// ── Konfigurasi Session ──────────────────────────────────────
define('SESSION_LIFETIME', 7200); // 2 jam (detik)

// ── Reset Password ───────────────────────────────────────────
define('RESET_TOKEN_EXPIRY_HOURS', 24);

// ── reCAPTCHA (pindah dari login.php) ────────────────────────
define('RECAPTCHA_V3_SITE_KEY',   '6LeDPREtAAAAACEj2e4x3l4-YKuv1BDLmtcTg8-Z');
define('RECAPTCHA_V3_SECRET_KEY', '6LeDPREtAAAAAGfYKRNLawJrT_oX-qw7TCPmjtL2');
define('RECAPTCHA_V3_MIN_SCORE',  0.5);
define('RECAPTCHA_V2_SITE_KEY',   '6LcvPxEtAAAAAGwQ4Owodey_oMcE8T6ipyGsNxQT');
define('RECAPTCHA_V2_SECRET_KEY', '6LcvPxEtAAAAAP-afS0nmzf6XCjoXZ9g1e1szSHa');

// ── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('Asia/Jakarta');

// ── Error Reporting ──────────────────────────────────────────
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASEPATH . '/storage/logs/php_error.log');
}

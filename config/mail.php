<?php
/**
 * Konfigurasi Email — Brevo (Sendinblue) SMTP
 */

defined('BASEPATH') or die('No direct script access allowed');

// ── Pilih driver: 'brevo' atau 'smtp' ────────────────────────
define('MAIL_DRIVER', 'smtp');

// ── Brevo SMTP ───────────────────────────────────────────────
define('BREVO_SMTP_HOST',     '');
define('BREVO_SMTP_PORT',     );
define('BREVO_SMTP_USER',     ''); // Ganti dengan login Brevo Anda
define('BREVO_SMTP_PASS',     '');        // Ganti dengan SMTP key dari Brevo
define('BREVO_SMTP_SECURE',   'tls');

// ── SMTP Server Sendiri (fallback) ───────────────────────────
define('SMTP_HOST',     '');
define('SMTP_PORT',     );
define('SMTP_USER',     '');
define('SMTP_PASS',     '');
define('SMTP_SECURE',   'tls');

// ── Identitas Pengirim ───────────────────────────────────────
define('MAIL_FROM_ADDRESS', '');
define('MAIL_FROM_NAME',    '');

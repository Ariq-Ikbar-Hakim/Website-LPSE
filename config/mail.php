<?php
/**
 * Konfigurasi Email — Brevo (Sendinblue) SMTP
 */

defined('BASEPATH') or die('No direct script access allowed');

// ── Pilih driver: 'brevo' atau 'smtp' ────────────────────────
define('MAIL_DRIVER', 'smtp');

// ── Brevo SMTP ───────────────────────────────────────────────
define('BREVO_SMTP_HOST',     'smtp-relay.brevo.com');
define('BREVO_SMTP_PORT',     587);
define('BREVO_SMTP_USER',     'aed137001@smtp-brevo.com'); // Ganti dengan login Brevo Anda
define('BREVO_SMTP_PASS',     'ZV2hRn89GySJbPpL');        // Ganti dengan SMTP key dari Brevo
define('BREVO_SMTP_SECURE',   'tls');

// ── SMTP Server Sendiri (fallback) ───────────────────────────
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     'ariq20055@gmail.com');
define('SMTP_PASS',     'qpjy ibna lvvt jinc');
define('SMTP_SECURE',   'tls');

// ── Identitas Pengirim ───────────────────────────────────────
define('MAIL_FROM_ADDRESS', 'ariq20055@gmail.com');
define('MAIL_FROM_NAME',    'LPSE APELBAJA');

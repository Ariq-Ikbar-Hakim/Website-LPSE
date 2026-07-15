<?php
/**
 * Front Controller (Router Sederhana)
 */
require_once __DIR__ . '/bootstrap.php';

$page = $_GET['page'] ?? 'dashboard';

// --- Router Sederhana ---
switch ($page) {
    // Auth
    case 'login':
        $controller = new AuthController($conn);
        $controller->login();
        break;
    case 'register':
        $controller = new AuthController($conn);
        $controller->register();
        break;
    case 'logout':
        $controller = new AuthController($conn);
        $controller->logout();
        break;
    case 'lupa_password':
        $controller = new AuthController($conn);
        $controller->lupaPassword();
        break;
    case 'reset_password':
        $controller = new AuthController($conn);
        $controller->resetPasswordForm();
        break;
    case 'update_password':
        $controller = new AuthController($conn);
        $controller->updatePassword();
        break;

    // Dashboard
    case 'dashboard':
        $controller = new DashboardController($conn);
        $controller->index();
        break;

    // Paket
    case 'paket_index':
        $controller = new PaketController($conn);
        $controller->index();
        break;
    case 'paket_buat':
        $controller = new PaketController($conn);
        $controller->buat();
        break;
    case 'paket_detail':
        $controller = new PaketController($conn);
        $controller->detail();
        break;
    case 'paket_kirim':
        $controller = new PaketController($conn);
        $controller->kirim();
        break;
    case 'paket_action':
        $controller = new PaketController($conn);
        $controller->action();
        break;

    // Lampiran
    case 'lampiran_upload':
        $controller = new LampiranController($conn);
        $controller->upload();
        break;
    case 'lampiran_review':
        $controller = new LampiranController($conn);
        $controller->review();
        break;

    // Berita Acara
    case 'ba_index':
        $controller = new BeritaAcaraController($conn);
        $controller->index();
        break;
    case 'ba_sign':
        $controller = new BeritaAcaraController($conn);
        $controller->sign();
        break;

    // Komentar
    case 'komentar_tambah':
        $controller = new KomentarController($conn);
        $controller->tambah();
        break;

    // Admin
    case 'admin_konfirmasi':
        $controller = new AdminController($conn);
        $controller->konfirmasiAkun();
        break;
    case 'admin_monitoring_usulan':
        $controller = new AdminController($conn);
        $controller->monitoringUsulan();
        break;
    case 'admin_reset_password':
        $controller = new AdminController($conn);
        $controller->resetPasswordRequests();
        break;
    // Transfer Jabatan & Paket
    case 'transfer_ajukan':
        $controller = new TransferPaketController($conn);
        $controller->ajukan();
        break;
    case 'transfer_saya':
        $controller = new TransferPaketController($conn);
        $controller->daftarPengajuanSaya();
        break;
    case 'admin_transfer_paket':
        $controller = new TransferPaketController($conn);
        $controller->adminDaftar();
        break;
    case 'admin_transfer_setujui':
        $controller = new TransferPaketController($conn);
        $controller->adminSetujui();
        break;
    case 'admin_transfer_tolak':
        $controller = new TransferPaketController($conn);
        $controller->adminTolak();
        break;

    default:
        http_response_code(404);
        echo "<h1 style='text-align:center;margin-top:50px;'>404 Halaman Tidak Ditemukan</h1>";
        break;
}

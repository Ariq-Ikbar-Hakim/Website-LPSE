<?php
/**
 * Controller TransferPaket — menangani routing untuk fitur transfer paket
 */
class TransferPaketController
{
    private TransferPaketService $transferService;
    private AssignmentTransfer $transferModel;
    private Paket $paketModel;
    private User $userModel;

    public function __construct(private mysqli $db)
    {
        $this->transferService = new TransferPaketService($db);
        $this->transferModel = new AssignmentTransfer($db);
        $this->paketModel = new Paket($db);
        $this->userModel = new User($db);
    }

    public function ajukan(): void
    {
        RoleMiddleware::requireRole('PP', 'PPK');

        $userId = (int)$_SESSION['user_id'];
        $roleSaatIni = strtolower($_SESSION['jabatan_aktif'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $userTujuanId = (int)$_POST['user_tujuan_id'];
            $alasan = trim($_POST['alasan']);
            $tipeTransfer = $roleSaatIni === 'ppk' ? 'ppk' : 'pp';

            $result = $this->transferService->ajukan($userId, $userTujuanId, $tipeTransfer, $alasan);

            if ($result['ok']) {
                flashSet('success', $result['message']);
                redirect('index.php?page=transfer_saya');
            } else {
                flashSet('error', $result['message']);
                redirect('index.php?page=transfer_ajukan');
            }
        }

        // Ambil semua user aktif (kecuali diri sendiri)
        $semuaUser = $this->userModel->getAll();
        $userTujuanList = array_filter($semuaUser, function($u) use ($userId) {
            return (int)$u['id'] !== $userId && (int)$u['status_aktif'] === 1 && $u['jabatan_aktif'] !== 'admin';
        });

        require BASEPATH . '/views/paket/transfer_ajukan.php';
    }

    public function daftarPengajuanSaya(): void
    {
        RoleMiddleware::requireRole('PP', 'PPK');
        $userId = (int)$_SESSION['user_id'];
        $riwayatTransfer = $this->transferModel->getHistoryByUser($userId);
        
        require BASEPATH . '/views/paket/transfer_riwayat.php';
    }

    public function adminDaftar(): void
    {
        RoleMiddleware::requireRole('admin');
        
        $pendingTransfers = $this->transferModel->getAllPending();
        
        require BASEPATH . '/views/admin/transfer_paket.php';
    }

    public function adminSetujui(): void
    {
        RoleMiddleware::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $transferId = (int)$_POST['transfer_id'];
            $catatan = trim($_POST['catatan_admin'] ?? '');
            $adminId = (int)$_SESSION['user_id'];

            $result = $this->transferService->setujui($transferId, $adminId, $catatan);

            if ($result['ok']) {
                flashSet('success', $result['message']);
            } else {
                flashSet('error', $result['message']);
            }
            redirect('index.php?page=admin_transfer_paket');
        }
    }

    public function adminTolak(): void
    {
        RoleMiddleware::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $transferId = (int)$_POST['transfer_id'];
            $catatan = trim($_POST['catatan_admin'] ?? '');
            $adminId = (int)$_SESSION['user_id'];

            $result = $this->transferService->tolak($transferId, $adminId, $catatan);

            if ($result['ok']) {
                flashSet('success', $result['message']);
            } else {
                flashSet('error', $result['message']);
            }
            redirect('index.php?page=admin_transfer_paket');
        }
    }
}

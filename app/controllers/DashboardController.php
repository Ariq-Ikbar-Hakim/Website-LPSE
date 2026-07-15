<?php
/**
 * DashboardController — menangani tampilan dashboard utama
 */
class DashboardController
{
    private Paket $paketModel;

    public function __construct(mysqli $db)
    {
        $this->paketModel = new Paket($db);
    }

    public function index(): void
    {
        AuthMiddleware::requireLogin();

        $role = getRole();
        $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
        $userId = $_SESSION['user_id'];

        $filters = ['tahun_anggaran' => $tahun, 'exclude_status' => 'draft'];
        
        if ($role === 'PPK') {
            $filters['ppk_id'] = $userId;
        } elseif ($role === 'PP') {
            $filters['pp_id'] = $userId;
        }

        $countsByStatus = $this->paketModel->countByStatus($filters);
        $countsByJenis = $this->paketModel->countByJenisPengadaan($filters);
        $totalPagu = $this->paketModel->getTotalPagu($filters);
        
        $totalPaket = array_sum($countsByStatus);
        
        if ($role === 'PPK') {
            $needAction = ($countsByStatus['perlu_revisi'] ?? 0);
            $draftCount = $this->paketModel->countByStatus(['tahun_anggaran' => $tahun, 'ppk_id' => $userId, 'status' => 'draft'])['draft'] ?? 0;
            $countsByStatus['draft'] = $draftCount;
            $totalPaket += $draftCount;
        } elseif ($role === 'PP') {
            $needAction = ($countsByStatus['dikirim'] ?? 0); // Paket yang harus dikaji
        } else {
            $needAction = 0; // Admin memonitor
        }

        $recentPaket = $this->paketModel->getAll($filters, 6);

        // Load view sesuai role
        if ($role === 'admin') {
            global $conn;
            $userModel = new User($conn);
            $pendingUsersCount = $userModel->countPending();
            require BASEPATH . '/views/dashboard/admin.php';
        } elseif ($role === 'PP') {
            require BASEPATH . '/views/dashboard/pp.php';
        } else {
            require BASEPATH . '/views/dashboard/ppk.php';
        }
    }
}

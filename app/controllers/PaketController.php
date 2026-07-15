<?php
/**
 * PaketController — manajemen data paket (CRUD)
 */
class PaketController
{
    private PaketService $paketService;
    private Paket $paketModel;
    private SirupService $sirupService;
    private User $userModel;

    public function __construct(mysqli $db)
    {
        $this->paketService = new PaketService($db);
        $this->paketModel = new Paket($db);
        $this->sirupService = new SirupService();
        $this->userModel = new User($db);
    }

    public function index(): void
    {
        AuthMiddleware::requireLogin();
        
        $role = getRole();
        $tahun = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? (int)$_GET['tahun'] : '';
        $bulan = $_GET['bulan'] ?? '';
        $status = $_GET['status'] ?? 'semua';
        $jenis = $_GET['jenis'] ?? '';
        $search = trim($_GET['q'] ?? '');

        $filters = [];
        if ($tahun !== '') $filters['tahun_anggaran'] = $tahun;
        if ($bulan !== '') $filters['bulan'] = $bulan;
        if ($role === 'PPK') $filters['ppk_id'] = $_SESSION['user_id'];
        if ($role === 'PP') {
            $filters['pp_id'] = $_SESSION['user_id'];
            if ($status === 'semua') $filters['exclude_status'] = 'draft';
        }
        if ($status !== 'semua') $filters['status'] = $status;
        else if ($role === 'admin') $filters['exclude_status'] = 'draft';
        if ($jenis !== '') $filters['jenis_pengadaan'] = $jenis;
        if ($search !== '') $filters['search'] = $search;

        $pakets = $this->paketModel->getAll($filters);
        
        // Count for tabs
        $countFilters = [];
        if ($tahun !== '') $countFilters['tahun_anggaran'] = $tahun;
        if ($bulan !== '') $countFilters['bulan'] = $bulan;
        if ($role === 'PPK') $countFilters['ppk_id'] = $_SESSION['user_id'];
        if ($role === 'PP') {
            $countFilters['pp_id'] = $_SESSION['user_id'];
            $countFilters['exclude_status'] = 'draft';
        }
        if ($role === 'admin') $countFilters['exclude_status'] = 'draft';

        $statusCounts = $this->paketModel->countByStatus($countFilters);

        // Jika PPK, tambahkan hitungan draft manual
        if ($role === 'PPK') {
            $draftCount = $this->paketModel->countByStatus(['tahun_anggaran' => $tahun, 'ppk_id' => $_SESSION['user_id'], 'status' => 'draft'])['draft'] ?? 0;
            $statusCounts['draft'] = $draftCount;
        }

        require BASEPATH . '/views/paket/index.php';
    }

    public function buat(): void
    {
        RoleMiddleware::requireRole('PPK');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            
            $data = [
                'ppk_id' => $_SESSION['user_id'],
                'pp_id' => (int)$_POST['pp_id'],
                'kode_rup' => trim($_POST['kode_rup']),
                'nama_paket' => trim($_POST['nama_paket']),
                'pagu' => (float)$_POST['pagu'],
                'hps' => 0.00, // PPK belum isi HPS
                'metode_pengadaan' => trim($_POST['metode_pengadaan']),
                'tahun_anggaran' => (int)$_POST['tahun_anggaran'],
                'sumber_dana' => trim($_POST['sumber_dana'] ?? 'APBD'),
                'jenis_pengadaan' => trim($_POST['jenis_pengadaan'] ?? 'JASA LAINNYA'),
                'jenis_kontrak' => trim($_POST['jenis_kontrak'] ?? ''),
                'keterangan' => trim($_POST['keterangan'] ?? '')
            ];

            if (empty($data['kode_rup']) || empty($data['nama_paket']) || empty($data['pp_id'])) {
                flashSet('error', 'Semua field wajib diisi.');
                redirect('index.php?page=paket_buat');
            }

            $id = $this->paketService->createPaket($data);
            if ($id) {
                flashSet('success', 'Draft paket berhasil dibuat. Silakan upload lampiran.');
                redirect('index.php?page=paket_detail&id=' . $id);
            } else {
                flashSet('error', 'Gagal membuat paket.');
                redirect('index.php?page=paket_buat');
            }
        }

        $listPP = $this->userModel->getActivePP();
        require BASEPATH . '/views/paket/buat.php';
    }

    public function detail(): void
    {
        AuthMiddleware::requireLogin();
        
        $id = (int)($_GET['id'] ?? 0);
        $paket = $this->paketModel->findById($id);

        if (!$paket) {
            flashSet('error', 'Paket tidak ditemukan.');
            redirect('index.php?page=paket_index');
        }

        // Authorization check
        $role = getRole();
        if ($role === 'PPK' && $paket['ppk_id'] != $_SESSION['user_id']) {
            die('Akses ditolak.');
        }
        if ($role === 'PP' && $paket['pp_id'] != $_SESSION['user_id']) {
            die('Akses ditolak.');
        }
        if ($role === 'PP' && $paket['status'] === 'draft') {
            flashSet('error', 'Paket belum dikirim oleh PPK.');
            redirect('index.php?page=paket_index');
        }

        if ($role === 'admin') {
            $this->paketModel->markAdminViewed($id);
            // Refresh paket data to get the updated dilihat_admin_at if needed, but we can just set it manually for the view
            $paket['dilihat_admin_at'] = date('Y-m-d H:i:s');
        }

        global $conn;
        $lampiranModel = new Lampiran($conn);
        $komentarModel = new Komentar($conn);
        $auditModel = new AuditLog($conn);

        $lampiranAktif = $lampiranModel->getActiveByPaket($id);
        $lampiranHistory = $lampiranModel->getAllHistoryByPaket($id);
        $komentar = $komentarModel->getByPaket($id);
        
        $logs = []; // Get specific audit logs for this packet
        $stmt = $conn->prepare("SELECT a.*, u.nama AS user_nama FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id WHERE a.tabel_terpengaruh = 'paket' AND a.record_id = ? ORDER BY a.created_at DESC");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        require BASEPATH . '/views/paket/detail.php';
    }

    public function kajiUlang(): void
    {
        RoleMiddleware::requireRole('PP');
        // Handle kaji ulang logic
    }

    public function kirim(): void
    {
        RoleMiddleware::requireRole('PPK');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $id = (int)$_POST['id'];
            $paket = $this->paketModel->findById($id);
            if (!$paket || $paket['ppk_id'] != $_SESSION['user_id']) {
                flashSet('error', 'Paket tidak ditemukan atau akses ditolak.');
                redirect('index.php?page=paket_index');
            }

            // Validasi lampiran minimal (contoh)
            global $conn;
            $lampiranModel = new Lampiran($conn);
            $lampiran = $lampiranModel->getActiveByPaket($id);
            if (empty($lampiran)) {
                flashSet('error', 'Harap unggah minimal 1 dokumen lampiran sebelum mengirim paket.');
                redirect('index.php?page=paket_detail&id=' . $id);
            }

            $ok = $this->paketService->submitToUkpbj($id, 'Paket dikirim ke PP untuk dikaji ulang.');
            if ($ok) {
                flashSet('success', 'Paket berhasil dikirim ke PP.');
            } else {
                flashSet('error', 'Gagal mengirim paket.');
            }
            redirect('index.php?page=paket_detail&id=' . $id);
        }
    }

    public function action(): void
    {
        RoleMiddleware::requireRole('PP');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $id = (int)$_POST['id'];
            $action = $_POST['action'] ?? '';
            $catatan = trim($_POST['catatan'] ?? '');

            $paket = $this->paketModel->findById($id);
            if (!$paket || $paket['pp_id'] != $_SESSION['user_id']) {
                flashSet('error', 'Akses ditolak.');
                redirect('index.php?page=paket_index');
            }

            global $conn;
            $komentarModel = new Komentar($conn);

            if ($action === 'setujui') {
                $this->paketService->updateStatus($id, 'disetujui', 'Paket disetujui oleh PP.');
                if ($catatan) {
                    $komentarModel->create([
                        'paket_id' => $id, 'user_id' => $_SESSION['user_id'],
                        'komentar' => $catatan, 'role_saat_komentar' => 'PP',
                        'lampiran_id' => null, 'is_monitoring' => 0
                    ]);
                }
                flashSet('success', 'Paket disetujui. Lanjut ke proses tanda tangan.');
            } elseif ($action === 'revisi') {
                $this->paketService->updateStatus($id, 'perlu_revisi', 'Paket dikembalikan ke PPK untuk revisi.');
                if ($catatan) {
                    $komentarModel->create([
                        'paket_id' => $id, 'user_id' => $_SESSION['user_id'],
                        'komentar' => $catatan, 'role_saat_komentar' => 'PP',
                        'lampiran_id' => null, 'is_monitoring' => 0
                    ]);
                }
                
                // Clear existing signatures and reset BA status if any
                $baModel = new BeritaAcara($conn);
                $ba = $baModel->findByPaketId($id);
                if ($ba) {
                    $conn->query("DELETE FROM signatures WHERE berita_acara_id = " . (int)$ba['id']);
                    $baModel->updateStatus($ba['id'], 'draft');
                }
                
                flashSet('success', 'Paket dikembalikan ke PPK untuk revisi.');
            } elseif ($action === 'batalkan') {
                $this->paketService->updateStatus($id, 'dibatalkan', 'Paket dibatalkan oleh PP.');
                if ($catatan) {
                    $komentarModel->create([
                        'paket_id' => $id, 'user_id' => $_SESSION['user_id'],
                        'komentar' => 'Pembatalan: ' . $catatan, 'role_saat_komentar' => 'PP',
                        'lampiran_id' => null, 'is_monitoring' => 0
                    ]);
                }
                
                // Clear existing signatures and reset BA status if any
                $baModel = new BeritaAcara($conn);
                $ba = $baModel->findByPaketId($id);
                if ($ba) {
                    $conn->query("DELETE FROM signatures WHERE berita_acara_id = " . (int)$ba['id']);
                    $baModel->updateStatus($ba['id'], 'draft');
                }
                
                flashSet('success', 'Paket berhasil dibatalkan.');
            }
            redirect('index.php?page=paket_detail&id=' . $id);
        }
    }
}

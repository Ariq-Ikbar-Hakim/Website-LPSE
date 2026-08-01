<?php
/**
 * BeritaAcaraController — manajemen BA
 */
class BeritaAcaraController
{
    private BeritaAcaraService $baService;

    public function __construct(mysqli $db)
    {
        $this->baService = new BeritaAcaraService($db);
    }

    public function index(): void
    {
        AuthMiddleware::requireLogin();
        $bulan = $_GET['bulan'] ?? '';
        $tahun = $_GET['tahun'] ?? date('Y');

        global $conn;
        $baModel = new BeritaAcara($conn);
        $listBa = $baModel->getAllCompleted(['bulan' => $bulan, 'tahun' => $tahun]);

        $role = getRole();
        $eligiblePackages = [];
        $activePPK = [];
        if ($role === 'PP') {
            $userId = $_SESSION['user_id'];
            $userModel = new User($conn);
            $activePPK = $userModel->getActivePPK();
            
            // Ambil paket yang sudah disetujui dan ditugaskan ke PP ini, yang belum memiliki Berita Acara (atau belum ditandatangani PP)
            $stmt = $conn->prepare("
                SELECT p.id, p.nama_paket 
                FROM paket p 
                LEFT JOIN berita_acara ba ON p.id = ba.paket_id
                WHERE p.status = 'disetujui' AND p.pp_id = ? 
                AND (ba.id IS NULL OR (ba.status != 'ditandatangani_pp' AND ba.status != 'tanda_tangan_kedua' AND ba.status != 'selesai'))
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $eligiblePackages[] = $row;
            }
        }

        require BASEPATH . '/views/berita_acara/index.php';
    }

    public function sign(): void
    {
        RoleMiddleware::requireRole('PP', 'PPK');
        verifyCsrf();

        $paketId = (int)$_POST['paket_id'];
        
        $signaturePath = '';
        if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['signature_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $uploadDir = BASEPATH . '/uploads/signatures/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'sig_' . $paketId . '_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                $target = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['signature_image']['tmp_name'], $target)) {
                    $signaturePath = 'uploads/signatures/' . $filename;
                }
            } else {
                flashSet('error', 'Format gambar tanda tangan tidak didukung (gunakan PNG/JPG).');
                redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara');
            }
        } else {
            flashSet('error', 'Gambar tanda tangan wajib diunggah.');
            redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara');
        }
        
        global $conn;
        
        // Validasi khusus untuk PPK saat menandatangani BA manual
        $role = getRole();
        if ($role === 'PPK') {
            $baModel = new BeritaAcara($conn);
            $ba = $baModel->findByPaketId($paketId);
            if ($ba && $ba['jenis_ba'] === 'manual') {
                $lampiranModel = new Lampiran($conn);
                $lampiranHistory = $lampiranModel->getByPaketId($paketId);
                $hasApprovedLampiran = false;
                foreach ($lampiranHistory as $l) {
                    if ($l['is_active'] && $l['status_validasi'] === 'disetujui') {
                        $hasApprovedLampiran = true;
                        break;
                    }
                }
                
                if (!$hasApprovedLampiran) {
                    flashSet('error', 'Gagal menandatangani. File lampiran wajib diunggah dan disetujui (oleh PP/Admin) sebelum menandatangani Berita Acara ini.');
                    redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara');
                    return;
                }
            }
        }

        if ($this->baService->sign($paketId, $signaturePath)) {
            flashSet('success', 'Berhasil menandatangani Berita Acara secara digital.');
        } else {
            flashSet('error', 'Gagal menandatangani. Mungkin Anda sudah tanda tangan.');
        }

        redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara');
    }

    public function uploadManual(): void
    {
        RoleMiddleware::requireRole('PP');
        verifyCsrf();

        // Cek apakah ini untuk paket yang sudah ada (dari detail.php)
        $paketId = isset($_POST['paket_id']) ? (int)$_POST['paket_id'] : 0;
        
        global $conn;
        $paketModel = new Paket($conn);

        if ($paketId > 0) {
            $paket = $paketModel->findById($paketId);
            if (!$paket) {
                flashSet('error', 'Paket tidak ditemukan.');
                redirect('index.php?page=ba_index');
            }
            $namaPaket = $paket['nama_paket'];
            $kodeRup = $paket['kode_rup'];
            $tahunAnggaran = $paket['tahun_anggaran'];
            $pagu = $paket['pagu'];
            $hps = $paket['hps'];
            $ppkId = $paket['ppk_id'];
        } else {
            // Data Paket Baru (dari modal ba_index)
            $namaPaket = trim($_POST['nama_paket'] ?? '');
            $kodeRup = trim($_POST['kode_rup'] ?? '');
            $tahunAnggaran = (int)($_POST['tahun_anggaran'] ?? 0);
            $pagu = (float)str_replace(['Rp', '.', ',', ' '], '', $_POST['pagu'] ?? '0');
            $hps = (float)str_replace(['Rp', '.', ',', ' '], '', $_POST['hps'] ?? '0');
            $ppkId = (int)($_POST['ppk_id'] ?? 0);
        }
        
        // Data Berita Acara
        $nomorBa = trim($_POST['nomor_ba']);
        $tanggalBa = trim($_POST['tanggal_ba']);
        $kontenBa = trim($_POST['konten_ba'] ?? '');
        
        // Auto-generate konten BA jika kosong (untuk form dari modal)
        if (empty($kontenBa)) {
            $tanggalFormatted = date('d', strtotime($tanggalBa)) . ' ' .
                ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)date('n', strtotime($tanggalBa)) - 1] .
                ' ' . date('Y', strtotime($tanggalBa));
            $kontenBa = "Pada hari ini, tanggal {$tanggalFormatted}, telah disetujui dokumen persiapan pengadaan paket dengan rincian sebagai berikut:\n\nNama Paket    : {$namaPaket}\nKode RUP      : {$kodeRup}\nTahun Anggaran: {$tahunAnggaran}\nPagu          : Rp " . number_format($pagu, 0, ',', '.') . "\nHPS           : Rp " . number_format($hps, 0, ',', '.') . "\n\nDemikian Berita Acara ini dibuat dan ditandatangani secara elektronik (QR Code) untuk dipergunakan sebagaimana mestinya.";
        }
        
        if (empty($nomorBa) || empty($tanggalBa) || !$ppkId) {
            flashSet('error', 'Semua kolom data paket dan Berita Acara wajib diisi.');
            redirect($paketId > 0 ? 'index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara' : 'index.php?page=ba_index');
        }

        $signaturePath = '';
        if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['signature_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $uploadDir = BASEPATH . '/uploads/signatures/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'sig_manual_' . time() . '_' . $_SESSION['user_id'] . '.' . $ext;
                $target = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['signature_image']['tmp_name'], $target)) {
                    $signaturePath = 'uploads/signatures/' . $filename;
                }
            } else {
                flashSet('error', 'Format gambar tanda tangan tidak didukung (gunakan PNG/JPG).');
                redirect($paketId > 0 ? 'index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara' : 'index.php?page=ba_index');
            }
        } else {
            flashSet('error', 'Gambar tanda tangan wajib diunggah.');
            redirect($paketId > 0 ? 'index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara' : 'index.php?page=ba_index');
        }

        $isNewPaket = false;
        if ($paketId === 0) {
            $paketData = [
                'ppk_id' => $ppkId,
                'pp_id' => $_SESSION['user_id'],
                'kode_rup' => $kodeRup,
                'nama_paket' => $namaPaket,
                'pagu' => $pagu,
                'hps' => $hps,
                'metode_pengadaan' => 'Manual (Dibuat PP)',
                'tahun_anggaran' => $tahunAnggaran,
                'sumber_dana' => 'APBD', 
                'jenis_pengadaan' => 'BARANG/JASA',
                'jenis_kontrak' => 'Lumsum',
                'keterangan' => 'Paket ini dibuat otomatis melalui fitur Upload BA Manual oleh PP'
            ];
            
            $paketId = $paketModel->create($paketData);
            if (!$paketId) {
                flashSet('error', 'Gagal membuat data paket baru.');
                redirect('index.php?page=ba_index');
            }
            $isNewPaket = true;
        }

        try {
            $res = $this->baService->generateManualInApp($paketId, $nomorBa, $tanggalBa, $kontenBa, $signaturePath);
            if ($res['success']) {
                flashSet('success', 'Berita Acara manual berhasil dibuat dan diteruskan ke PPK.');
            } else {
                flashSet('error', 'Gagal membuat dokumen Berita Acara manual.');
            }
        } catch (\Exception $e) {
            // Jika gagal buat BA dan ini paket baru, hapus paket yang terlanjur dibuat
            if ($isNewPaket) {
                $paketModel->delete($paketId); 
            }
            flashSet('error', $e->getMessage());
            redirect($isNewPaket ? 'index.php?page=ba_index' : 'index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara');
        }

        redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara');
    }
}

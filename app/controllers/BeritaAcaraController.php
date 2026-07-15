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
        
        if ($this->baService->sign($paketId, $signaturePath)) {
            flashSet('success', 'Berhasil menandatangani Berita Acara secara digital.');
        } else {
            flashSet('error', 'Gagal menandatangani. Mungkin Anda sudah tanda tangan.');
        }

        redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=berita_acara');
    }
}

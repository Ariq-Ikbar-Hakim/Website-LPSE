<?php
/**
 * LampiranController — manajemen upload, versioning, review lampiran
 */
class LampiranController
{
    private LampiranService $lampiranService;

    public function __construct(mysqli $db)
    {
        $this->lampiranService = new LampiranService($db);
    }

    public function upload(): void
    {
        RoleMiddleware::requireRole('PPK');
        verifyCsrf();

        $paketId = (int)$_POST['paket_id'];
        $tipe = $_POST['tipe_dokumen'];
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['file']['tmp_name'];
            $name = $_FILES['file']['name'];
            $size = $_FILES['file']['size'];
            $mime = $_FILES['file']['type'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if (!in_array($ext, UPLOAD_ALLOWED_EXT)) {
                flashSet('error', 'Ekstensi file tidak diizinkan.');
                redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=lampiran');
            }

            if ($size > UPLOAD_MAX_SIZE) {
                flashSet('error', 'Ukuran file maksimal 10MB.');
                redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=lampiran');
            }

            // Versioning Name
            global $conn;
            $lModel = new Lampiran($conn);
            $nextVers = $lModel->getNextVersion($paketId, $tipe);
            
            $newName = "paket_{$paketId}_" . time() . "_rev{$nextVers}.{$ext}";
            $targetPath = UPLOAD_PATH_LAMPIRAN . $newName;

            if (move_uploaded_file($tmpPath, $targetPath)) {
                $fileInfo = [
                    'name' => $name,
                    'file_path' => $targetPath,
                    'size' => $size,
                    'type' => $mime
                ];
                $this->lampiranService->upload($paketId, $tipe, $fileInfo);
                flashSet('success', 'File berhasil diunggah.');
            } else {
                flashSet('error', 'Gagal memindahkan file upload.');
            }
        }
        
        redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=lampiran');
    }

    public function review(): void
    {
        RoleMiddleware::requireRole('PP');
        verifyCsrf();

        $lampiranId = (int)$_POST['lampiran_id'];
        $paketId = (int)$_POST['paket_id'];
        $status = $_POST['status_validasi']; // 'disetujui' atau 'revisi'
        $catatan = trim($_POST['catatan'] ?? '');

        if ($status === 'revisi' && empty($catatan)) {
            flashSet('error', 'Catatan revisi wajib diisi.');
            redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=lampiran');
        }

        $this->lampiranService->review($lampiranId, $status, $catatan);
        flashSet('success', 'Review lampiran berhasil disimpan.');
        
        redirect('index.php?page=paket_detail&id=' . $paketId . '&tab=lampiran');
    }
}

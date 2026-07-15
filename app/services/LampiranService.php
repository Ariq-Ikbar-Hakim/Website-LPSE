<?php
/**
 * Service Lampiran — logika bisnis lampiran & versioning
 */
class LampiranService
{
    private Lampiran $lampiranModel;
    private AuditService $auditService;

    public function __construct(mysqli $db)
    {
        $this->lampiranModel = new Lampiran($db);
        $this->auditService = new AuditService($db);
    }

    public function upload(int $paketId, string $tipeDokumen, array $fileInfo): bool
    {
        // Nonaktifkan versi sebelumnya jika ada
        $this->lampiranModel->deactivatePrevious($paketId, $tipeDokumen);
        
        $nextVersion = $this->lampiranModel->getNextVersion($paketId, $tipeDokumen);

        $data = [
            'paket_id' => $paketId,
            'tipe_dokumen' => $tipeDokumen,
            'versi' => $nextVersion,
            'nama_asli' => $fileInfo['name'],
            'nama_file' => basename($fileInfo['file_path']),
            'file_path' => $fileInfo['file_path'],
            'ukuran_file' => $fileInfo['size'],
            'mime_type' => $fileInfo['type'],
            'is_active' => 1,
            'status_validasi' => 'menunggu',
            'uploaded_by' => $_SESSION['user_id']
        ];

        $id = $this->lampiranModel->create($data);
        if ($id) {
            $this->auditService->log('UPLOAD', 'lampiran', $id, null, $data, "Upload dokumen {$tipeDokumen} v{$nextVersion}");
            return true;
        }
        return false;
    }

    public function review(int $id, string $status, string $catatan = ''): bool
    {
        $old = $this->lampiranModel->findById($id);
        if (!$old) return false;

        $ok = $this->lampiranModel->updateStatus($id, $status);
        
        if ($ok && $catatan !== '') {
            // Insert ke komentar
            global $conn;
            $komentarModel = new Komentar($conn);
            $komentarModel->create([
                'paket_id' => $old['paket_id'],
                'lampiran_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'role_saat_komentar' => $_SESSION['jabatan_aktif'],
                'komentar' => $catatan,
                'is_monitoring' => 0
            ]);
        }

        if ($ok) {
            $this->auditService->log('UPDATE', 'lampiran', $id, ['status_validasi' => $old['status_validasi']], ['status_validasi' => $status], "Review lampiran: " . $status);
        }
        return $ok;
    }
}

<?php
/**
 * Model Lampiran — operasi database tabel lampiran
 */
class Lampiran
{
    public function __construct(private mysqli $db) {}

    public function getActiveByPaket(int $paketId): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.nama AS uploader_name
             FROM lampiran l
             JOIN users u ON l.uploaded_by = u.id
             WHERE l.paket_id = ? AND l.is_active = 1
             ORDER BY l.tipe_dokumen ASC'
        );
        $stmt->bind_param('i', $paketId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getAllHistoryByPaket(int $paketId): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.nama AS uploader_name
             FROM lampiran l
             JOIN users u ON l.uploaded_by = u.id
             WHERE l.paket_id = ?
             ORDER BY l.tipe_dokumen ASC, l.versi DESC'
        );
        $stmt->bind_param('i', $paketId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getHistoryByTipe(int $paketId, string $tipeDokumen): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.nama AS uploader_name
             FROM lampiran l
             JOIN users u ON l.uploaded_by = u.id
             WHERE l.paket_id = ? AND l.tipe_dokumen = ?
             ORDER BY l.versi DESC'
        );
        $stmt->bind_param('is', $paketId, $tipeDokumen);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lampiran WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    public function deactivatePrevious(int $paketId, string $tipeDokumen): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE lampiran SET is_active = 0 WHERE paket_id = ? AND tipe_dokumen = ?'
        );
        $stmt->bind_param('is', $paketId, $tipeDokumen);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getNextVersion(int $paketId, string $tipeDokumen): int
    {
        $stmt = $this->db->prepare(
            'SELECT MAX(versi) as max_v FROM lampiran WHERE paket_id = ? AND tipe_dokumen = ?'
        );
        $stmt->bind_param('is', $paketId, $tipeDokumen);
        $stmt->execute();
        $max = $stmt->get_result()->fetch_assoc()['max_v'] ?? 0;
        $stmt->close();
        return $max + 1;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lampiran
             (paket_id, tipe_dokumen, versi, nama_asli, nama_file, file_path, ukuran_file, mime_type, is_active, status_validasi, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'isisssisisi',
            $data['paket_id'],
            $data['tipe_dokumen'],
            $data['versi'],
            $data['nama_asli'],
            $data['nama_file'],
            $data['file_path'],
            $data['ukuran_file'],
            $data['mime_type'],
            $data['is_active'],
            $data['status_validasi'],
            $data['uploaded_by']
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE lampiran SET status_validasi = ? WHERE id = ?'
        );
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

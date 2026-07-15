<?php
/**
 * Model Komentar — operasi database tabel document_comments
 */
class Komentar
{
    public function __construct(private mysqli $db) {}

    public function getByPaket(int $paketId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.nama AS pengirim_nama,
                    l.tipe_dokumen, l.nama_file
             FROM document_comments c
             JOIN users u ON c.user_id = u.id
             LEFT JOIN lampiran l ON c.lampiran_id = l.id
             WHERE c.paket_id = ?
             ORDER BY c.created_at DESC'
        );
        $stmt->bind_param('i', $paketId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO document_comments
             (paket_id, lampiran_id, user_id, role_saat_komentar, komentar, is_monitoring)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iiissi',
            $data['paket_id'],
            $data['lampiran_id'],
            $data['user_id'],
            $data['role_saat_komentar'],
            $data['komentar'],
            $data['is_monitoring']
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }
}

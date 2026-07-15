<?php
/**
 * Model Signature — operasi database tabel signatures
 */
class Signature
{
    public function __construct(private mysqli $db) {}

    public function getByBeritaAcaraId(int $baId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, u.nama, u.nip, u.jabatan_aktif
             FROM signatures s
             JOIN users u ON s.user_id = u.id
             WHERE s.berita_acara_id = ?
             ORDER BY s.urutan ASC'
        );
        $stmt->bind_param('i', $baId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function hasSigned(int $baId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM signatures WHERE berita_acara_id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->bind_param('ii', $baId, $userId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO signatures
             (berita_acara_id, user_id, role_penandatangan, urutan, qr_data, qr_image_path, hash_dokumen, signed_at, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iisisssss',
            $data['berita_acara_id'],
            $data['user_id'],
            $data['role_penandatangan'],
            $data['urutan'],
            $data['qr_data'],
            $data['qr_image_path'],
            $data['hash_dokumen'],
            $data['signed_at'],
            $data['ip_address']
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }
}

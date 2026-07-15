<?php
/**
 * Model AuditLog — operasi database tabel audit_logs
 */
class AuditLog
{
    public function __construct(private mysqli $db) {}

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs
             (user_id, role_saat_aksi, tabel_terpengaruh, record_id, aksi, detail_lama, detail_baru, keterangan, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ississssss',
            $data['user_id'],
            $data['role_saat_aksi'],
            $data['tabel_terpengaruh'],
            $data['record_id'],
            $data['aksi'],
            $data['detail_lama'],
            $data['detail_baru'],
            $data['keterangan'],
            $data['ip_address'],
            $data['user_agent']
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.nama AS user_nama
             FROM audit_logs a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}

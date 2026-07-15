<?php
/**
 * Model AssignmentTransfer — operasi database tabel assignment_transfer
 */
class AssignmentTransfer
{
    public function __construct(private mysqli $db) {}

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assignment_transfer
             (dari_user_id, ke_user_id, tipe_transfer, alasan, status)
             VALUES (?, ?, ?, ?, ?)'
        );
        $status = 'menunggu';
        $stmt->bind_param(
            'iisss',
            $data['dari_user_id'],
            $data['ke_user_id'],
            $data['tipe_transfer'],
            $data['alasan'],
            $status
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, 
                    u_dari.nama AS nama_dari, u_dari.jabatan_aktif AS role_dari,
                    u_ke.nama AS nama_ke, u_ke.jabatan_aktif AS role_ke, u_ke.email AS email_ke
             FROM assignment_transfer a
             JOIN users u_dari ON a.dari_user_id = u_dari.id
             JOIN users u_ke ON a.ke_user_id = u_ke.id
             WHERE a.id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    public function getPendingByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM assignment_transfer 
             WHERE dari_user_id = ? AND status = "menunggu" LIMIT 1'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    public function getAllPending(): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, 
                    u_dari.nama AS nama_dari, u_dari.jabatan_aktif AS role_dari,
                    u_ke.nama AS nama_ke, u_ke.jabatan_aktif AS role_ke
             FROM assignment_transfer a
             JOIN users u_dari ON a.dari_user_id = u_dari.id
             JOIN users u_ke ON a.ke_user_id = u_ke.id
             WHERE a.status = "menunggu"
             ORDER BY a.created_at ASC'
        );
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getHistoryByPaket(int $paketId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, 
                    u_dari.nama AS nama_dari,
                    u_ke.nama AS nama_ke,
                    u_admin.nama AS nama_admin
             FROM assignment_transfer a
             JOIN users u_dari ON a.dari_user_id = u_dari.id
             JOIN users u_ke ON a.ke_user_id = u_ke.id
             LEFT JOIN users u_admin ON a.disetujui_oleh = u_admin.id
             WHERE a.paket_id = ?
             ORDER BY a.created_at DESC'
        );
        $stmt->bind_param('i', $paketId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getHistoryByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, 
                    u_dari.nama AS nama_dari,
                    u_ke.nama AS nama_ke
             FROM assignment_transfer a
             JOIN users u_dari ON a.dari_user_id = u_dari.id
             JOIN users u_ke ON a.ke_user_id = u_ke.id
             WHERE a.dari_user_id = ? OR a.ke_user_id = ?
             ORDER BY a.created_at DESC'
        );
        $stmt->bind_param('ii', $userId, $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function approve(int $id, int $adminId, ?string $catatan = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE assignment_transfer 
             SET status = "disetujui", disetujui_oleh = ?, catatan_admin = ?, disetujui_at = NOW() 
             WHERE id = ?'
        );
        $stmt->bind_param('isi', $adminId, $catatan, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function reject(int $id, int $adminId, string $catatan): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE assignment_transfer 
             SET status = "ditolak", disetujui_oleh = ?, catatan_admin = ?, disetujui_at = NOW() 
             WHERE id = ?'
        );
        $stmt->bind_param('isi', $adminId, $catatan, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

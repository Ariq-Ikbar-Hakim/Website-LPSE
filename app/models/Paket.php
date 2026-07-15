<?php
/**
 * Model Paket — operasi database tabel paket
 */
class Paket
{
    public function __construct(private mysqli $db) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    u_ppk.nama AS nama_ppk, u_ppk.opd AS opd_ppk,
                    u_pp.nama AS nama_pp, u_pp.opd AS opd_pp
             FROM paket p
             JOIN users u_ppk ON p.ppk_id = u_ppk.id
             JOIN users u_pp ON p.pp_id = u_pp.id
             WHERE p.id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    public function getAll(array $filters = [], int $limit = 0, int $offset = 0): array
    {
        $sql = 'SELECT p.*, u_ppk.nama AS nama_ppk, u_pp.nama AS nama_pp
                FROM paket p
                JOIN users u_ppk ON p.ppk_id = u_ppk.id
                JOIN users u_pp ON p.pp_id = u_pp.id
                WHERE 1=1';
        $params = [];
        $types  = '';

        if (!empty($filters['tahun_anggaran'])) {
            $sql .= ' AND p.tahun_anggaran = ?';
            $params[] = $filters['tahun_anggaran'];
            $types .= 'i';
        }
        if (!empty($filters['bulan'])) {
            $sql .= ' AND MONTH(p.created_at) = ?';
            $params[] = $filters['bulan'];
            $types .= 's';
        }
        if (!empty($filters['ppk_id'])) {
            $sql .= ' AND p.ppk_id = ?';
            $params[] = $filters['ppk_id'];
            $types .= 'i';
        }
        if (!empty($filters['pp_id'])) {
            $sql .= ' AND p.pp_id = ?';
            $params[] = $filters['pp_id'];
            $types .= 'i';
        }
        if (!empty($filters['status']) && $filters['status'] !== 'semua') {
            $sql .= ' AND p.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        } else if (!empty($filters['exclude_status'])) {
            $sql .= ' AND p.status != ?';
            $params[] = $filters['exclude_status'];
            $types .= 's';
        }
        if (!empty($filters['jenis_pengadaan'])) {
            $sql .= ' AND p.jenis_pengadaan = ?';
            $params[] = $filters['jenis_pengadaan'];
            $types .= 's';
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND p.nama_paket LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
            $types .= 's';
        }

        $sql .= ' ORDER BY p.created_at DESC';

        if ($limit > 0) {
            $sql .= ' LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function countByStatus(array $filters = []): array
    {
        $sql = 'SELECT status, COUNT(*) as total FROM paket p WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['tahun_anggaran'])) {
            $sql .= ' AND p.tahun_anggaran = ?';
            $params[] = $filters['tahun_anggaran'];
            $types .= 'i';
        }
        if (!empty($filters['bulan'])) {
            $sql .= ' AND MONTH(p.created_at) = ?';
            $params[] = $filters['bulan'];
            $types .= 's';
        }
        if (!empty($filters['ppk_id'])) {
            $sql .= ' AND p.ppk_id = ?';
            $params[] = $filters['ppk_id'];
            $types .= 'i';
        }
        if (!empty($filters['pp_id'])) {
            $sql .= ' AND p.pp_id = ?';
            $params[] = $filters['pp_id'];
            $types .= 'i';
        }
        if (!empty($filters['exclude_status'])) {
            $sql .= ' AND p.status != ?';
            $params[] = $filters['exclude_status'];
            $types .= 's';
        }

        $sql .= ' GROUP BY status';

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $counts = [
            'draft' => 0, 'dikirim' => 0, 'kaji_ulang' => 0, 'perlu_revisi' => 0,
            'disetujui' => 0, 'selesai' => 0, 'gagal_pemilihan' => 0, 'dibatalkan' => 0
        ];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = (int)$row['total'];
        }
        $stmt->close();
        return $counts;
    }

    public function countByJenisPengadaan(array $filters = []): array
    {
        $sql = 'SELECT jenis_pengadaan, COUNT(*) as total FROM paket p WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['tahun_anggaran'])) {
            $sql .= ' AND p.tahun_anggaran = ?';
            $params[] = $filters['tahun_anggaran'];
            $types .= 'i';
        }
        if (!empty($filters['ppk_id'])) {
            $sql .= ' AND p.ppk_id = ?';
            $params[] = $filters['ppk_id'];
            $types .= 'i';
        }
        if (!empty($filters['pp_id'])) {
            $sql .= ' AND p.pp_id = ?';
            $params[] = $filters['pp_id'];
            $types .= 'i';
        }
        if (!empty($filters['exclude_status'])) {
            $sql .= ' AND p.status != ?';
            $params[] = $filters['exclude_status'];
            $types .= 's';
        }

        $sql .= ' GROUP BY jenis_pengadaan';

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['jenis_pengadaan']] = (int)$row['total'];
        }
        $stmt->close();
        return $counts;
    }

    public function getTotalPagu(array $filters = []): float
    {
        $sql = 'SELECT COALESCE(SUM(pagu), 0) AS total_pagu FROM paket p WHERE 1=1';
        $params = [];
        $types = '';

        if (!empty($filters['tahun_anggaran'])) {
            $sql .= ' AND p.tahun_anggaran = ?';
            $params[] = $filters['tahun_anggaran'];
            $types .= 'i';
        }
        if (!empty($filters['ppk_id'])) {
            $sql .= ' AND p.ppk_id = ?';
            $params[] = $filters['ppk_id'];
            $types .= 'i';
        }
        if (!empty($filters['pp_id'])) {
            $sql .= ' AND p.pp_id = ?';
            $params[] = $filters['pp_id'];
            $types .= 'i';
        }
        if (!empty($filters['exclude_status'])) {
            $sql .= ' AND p.status != ?';
            $params[] = $filters['exclude_status'];
            $types .= 's';
        }

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total_pagu'] ?? 0;
        $stmt->close();
        return (float)$total;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO paket
             (ppk_id, pp_id, kode_rup, nama_paket, pagu, hps, metode_pengadaan,
              tahun_anggaran, sumber_dana, jenis_pengadaan, jenis_kontrak,
              keterangan, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        
        $status = 'draft';
        $stmt->bind_param(
            'iissddsisssss',
            $data['ppk_id'],
            $data['pp_id'],
            $data['kode_rup'],
            $data['nama_paket'],
            $data['pagu'],
            $data['hps'],
            $data['metode_pengadaan'],
            $data['tahun_anggaran'],
            $data['sumber_dana'],
            $data['jenis_pengadaan'],
            $data['jenis_kontrak'],
            $data['keterangan'],
            $status
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    public function updateStatus(int $id, string $status, ?string $catatan = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE paket SET status = ?, catatan_koreksi = ? WHERE id = ?'
        );
        $stmt->bind_param('ssi', $status, $catatan, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updateRup(int $id, string $kodeRup): bool
    {
        $stmt = $this->db->prepare('UPDATE paket SET kode_rup = ? WHERE id = ?');
        $stmt->bind_param('si', $kodeRup, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    public function markAdminViewed(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE paket SET dilihat_admin_at = NOW() WHERE id = ? AND dilihat_admin_at IS NULL'
        );
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

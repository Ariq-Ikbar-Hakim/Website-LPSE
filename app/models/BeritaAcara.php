<?php
/**
 * Model BeritaAcara — operasi database tabel berita_acara
 */
class BeritaAcara
{
    public function __construct(private mysqli $db) {}

    public function findByPaketId(int $paketId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM berita_acara WHERE paket_id = ? LIMIT 1');
        $stmt->bind_param('i', $paketId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ba.*, p.nama_paket, p.kode_rup
             FROM berita_acara ba
             JOIN paket p ON ba.paket_id = p.id
             WHERE ba.id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO berita_acara
             (paket_id, nomor_ba, tanggal_ba, konten, hash_konten, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'isssss',
            $data['paket_id'],
            $data['nomor_ba'],
            $data['tanggal_ba'],
            $data['konten'],
            $data['hash_konten'],
            $data['status']
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE berita_acara SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updateFileLaporan(int $id, string $filePath): bool
    {
        $stmt = $this->db->prepare('UPDATE berita_acara SET file_laporan = ? WHERE id = ?');
        $stmt->bind_param('si', $filePath, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getAllCompleted(array $filters = []): array
    {
        $sql = "SELECT ba.*, p.nama_paket, p.kode_rup
             FROM berita_acara ba
             JOIN paket p ON ba.paket_id = p.id
             WHERE (ba.file_laporan IS NOT NULL OR ba.status IN ('tanda_tangan_kedua', 'selesai'))";
        
        $params = [];
        $types = '';

        if (!empty($filters['bulan'])) {
            $sql .= " AND MONTH(ba.tanggal_ba) = ?";
            $params[] = $filters['bulan'];
            $types .= 's';
        }

        if (!empty($filters['tahun'])) {
            $sql .= " AND YEAR(ba.tanggal_ba) = ?";
            $params[] = $filters['tahun'];
            $types .= 's';
        }

        $sql .= " ORDER BY ba.id DESC";

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}

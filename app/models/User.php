<?php
/**
 * Model User — operasi database tabel users
 */
class User
{
    public function __construct(private mysqli $db) {}

    // ── Ambil satu user berdasarkan ID ───────────────────────
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    // ── Ambil user berdasarkan NIP ───────────────────────────
    public function findByNip(string $nip): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE nip = ? LIMIT 1'
        );
        $stmt->bind_param('s', $nip);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    // ── Ambil semua user PP yang aktif (untuk dropdown) ──────
    public function getActivePP(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nama, opd, nip
             FROM users
             WHERE jabatan_aktif = 'PP' AND status_aktif = 1
             ORDER BY nama ASC"
        );
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // ── Ambil semua user aktif (admin view) ──────────────────
    public function getAll(string $filter = '', int $limit = 0, int $offset = 0): array
    {
        $sql    = 'SELECT * FROM users WHERE 1=1';
        $params = [];
        $types  = '';

        if ($filter === 'menunggu') {
            $sql .= ' AND status_aktif = 0';
        } elseif (in_array($filter, ['PPK', 'PP', 'admin'], true)) {
            $sql .= ' AND jabatan_aktif = ?';
            $params[] = $filter;
            $types   .= 's';
        }

        $sql .= ' ORDER BY created_at DESC';

        if ($limit > 0) {
            $sql     .= ' LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;
            $types   .= 'ii';
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

    // ── Hitung user menunggu verifikasi ──────────────────────
    public function countPending(): int
    {
        $result = $this->db->query(
            'SELECT COUNT(*) AS c FROM users WHERE status_aktif = 0'
        );
        return (int) ($result->fetch_assoc()['c'] ?? 0);
    }

    // ── Daftarkan user baru ───────────────────────────────────
    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users
             (nip, nama, email, password, no_telp, opd, sub_unit_opd, jabatan_aktif,
              sk_nomor, sk_mulai, sk_sampai, keterangan, status_aktif)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0)'
        );
        $stmt->bind_param(
            'ssssssssssss',
            $data['nip'],
            $data['nama'],
            $data['email'],
            $data['password'],
            $data['no_telp'],
            $data['opd'],
            $data['sub_unit_opd'],
            $data['jabatan_aktif'],
            $data['sk_nomor'],
            $data['sk_mulai'],
            $data['sk_sampai'],
            $data['keterangan']
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }

    // ── Aktifkan / nonaktifkan akun ───────────────────────────
    public function setActive(int $id, int $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET status_aktif = ? WHERE id = ?'
        );
        $stmt->bind_param('ii', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ── Ubah jabatan user ─────────────────────────────────────
    public function updateJabatan(int $id, string $jabatan): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET jabatan_aktif = ? WHERE id = ?"
        );
        $stmt->bind_param('si', $jabatan, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ── Update password ───────────────────────────────────────
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET password = ?, reset_token = NULL,
             reset_token_expires = NULL WHERE id = ?'
        );
        $stmt->bind_param('si', $hashedPassword, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ── Simpan token reset password ───────────────────────────
    public function saveResetToken(int $id, string $token, string $expires): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = ?, reset_token_expires = ?,
             reset_requested_at = NOW() WHERE id = ?'
        );
        $stmt->bind_param('ssi', $token, $expires, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ── Cari user berdasarkan token reset ─────────────────────
    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users
             WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1'
        );
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    // ── Update last_login ─────────────────────────────────────
    public function updateLastLogin(int $id): void
    {
        $this->db->query("UPDATE users SET last_login = NOW() WHERE id = $id");
    }

    // ── NIP sudah ada? ────────────────────────────────────────
    public function nipExists(string $nip, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM users WHERE nip = ? AND id != ? LIMIT 1'
        );
        $stmt->bind_param('si', $nip, $excludeId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // ── Email sudah ada? ──────────────────────────────────────
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1'
        );
        $stmt->bind_param('si', $email, $excludeId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // ── Riwayat jabatan ───────────────────────────────────────
    public function getRoleHistory(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, u.nama AS diubah_oleh_nama
             FROM user_role_history h
             LEFT JOIN users u ON h.diubah_oleh = u.id
             WHERE h.user_id = ?
             ORDER BY h.created_at DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}

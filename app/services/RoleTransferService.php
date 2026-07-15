<?php
/**
 * Service RoleTransfer — memindahkan jabatan dan alih tugas
 */
class RoleTransferService
{
    private AuditService $auditService;

    public function __construct(private mysqli $db)
    {
        $this->auditService = new AuditService($db);
    }

    public function transferJabatan(int $userId, string $roleBaru, string $alasan): bool
    {
        $userModel = new User($this->db);
        $oldUser = $userModel->findById($userId);
        
        if (!$oldUser || $oldUser['jabatan_aktif'] === $roleBaru) {
            return false;
        }

        $roleLama = $oldUser['jabatan_aktif'];

        $this->db->begin_transaction();
        try {
            // Update users table
            $stmt1 = $this->db->prepare("UPDATE users SET jabatan_aktif = ? WHERE id = ?");
            $stmt1->bind_param('si', $roleBaru, $userId);
            $stmt1->execute();
            $stmt1->close();

            // Insert to user_role_history
            $stmt2 = $this->db->prepare(
                "INSERT INTO user_role_history (user_id, role_lama, role_baru, alasan, diubah_oleh)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $adminId = $_SESSION['user_id'];
            $stmt2->bind_param('isssi', $userId, $roleLama, $roleBaru, $alasan, $adminId);
            $stmt2->execute();
            $stmt2->close();

            $this->db->commit();

            $this->auditService->log(
                'ROLE_CHANGE', 'users', $userId,
                ['jabatan_aktif' => $roleLama],
                ['jabatan_aktif' => $roleBaru],
                "Ubah jabatan dari $roleLama ke $roleBaru. Alasan: $alasan"
            );

            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function approveRequest(int $requestId, ?string $catatan = null): array
    {
        require_once BASEPATH . '/app/models/RoleChangeRequest.php';
        $rcModel = new RoleChangeRequest($this->db);
        $req = $rcModel->findById($requestId);
        
        if (!$req || $req['status'] !== 'menunggu') {
            return ['success' => false, 'message' => 'Pengajuan tidak valid atau sudah diproses.'];
        }

        $userId = (int)$req['user_id'];
        $roleBaru = $req['role_tujuan'];
        $adminId = $_SESSION['user_id'] ?? null;

        $this->db->begin_transaction();
        try {
            // Update request status
            $stmt1 = $this->db->prepare("UPDATE role_change_requests SET status = 'disetujui', catatan_admin = ?, disetujui_oleh = ?, disetujui_at = NOW() WHERE id = ?");
            $stmt1->bind_param("sii", $catatan, $adminId, $requestId);
            $stmt1->execute();

            // Insert ke tabel sk_opd
            $stmt2 = $this->db->prepare("INSERT INTO sk_opd (user_id, nomor_sk, tanggal_sk, berlaku_dari, berlaku_sampai, file_sk, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ketSK = "SK baru dari pengajuan ganti jabatan ke " . $roleBaru;
            $stmt2->bind_param("issssss", $userId, $req['sk_nomor'], $req['sk_tanggal'], $req['sk_berlaku_dari'], $req['sk_berlaku_sampai'], $req['file_sk'], $ketSK);
            $stmt2->execute();

            // Update data SK di users
            $stmt3 = $this->db->prepare("UPDATE users SET sk_nomor = ?, sk_mulai = ?, sk_sampai = ?, sk_file = ? WHERE id = ?");
            $stmt3->bind_param("ssssi", $req['sk_nomor'], $req['sk_berlaku_dari'], $req['sk_berlaku_sampai'], $req['file_sk'], $userId);
            $stmt3->execute();

            // Call transferJabatan (this handles users.jabatan_aktif and user_role_history)
            // But transferJabatan uses begin_transaction inside, so we need to be careful with nested transactions.
            // In mysqli, nested transactions are not supported directly unless we use savepoints.
            // Let's just inline the transferJabatan logic here to avoid transaction conflict.
            
            $roleLama = $req['role_sekarang'];
            if ($roleLama !== $roleBaru) {
                $stmt4 = $this->db->prepare("UPDATE users SET jabatan_aktif = ? WHERE id = ?");
                $stmt4->bind_param("si", $roleBaru, $userId);
                $stmt4->execute();

                $stmt5 = $this->db->prepare("INSERT INTO user_role_history (user_id, role_lama, role_baru, alasan, diubah_oleh) VALUES (?, ?, ?, ?, ?)");
                $alasanRole = "Disetujui dari pengajuan ganti jabatan. Alasan user: " . $req['alasan'];
                $stmt5->bind_param("isssi", $userId, $roleLama, $roleBaru, $alasanRole, $adminId);
                $stmt5->execute();
                
                $this->auditService->log('ROLE_CHANGE', 'users', $userId, ['jabatan_aktif' => $roleLama], ['jabatan_aktif' => $roleBaru], "Ubah jabatan dari $roleLama ke $roleBaru via Pengajuan");
            }

            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Gagal memproses pengajuan: ' . $e->getMessage()];
        }
    }

    public function rejectRequest(int $requestId, string $catatan): array
    {
        require_once BASEPATH . '/app/models/RoleChangeRequest.php';
        $rcModel = new RoleChangeRequest($this->db);
        $req = $rcModel->findById($requestId);
        
        if (!$req || $req['status'] !== 'menunggu') {
            return ['success' => false, 'message' => 'Pengajuan tidak valid atau sudah diproses.'];
        }

        $adminId = $_SESSION['user_id'] ?? null;
        $success = $rcModel->updateStatus($requestId, 'ditolak', $catatan, $adminId);

        if ($success) {
            $this->auditService->log('REJECT', 'role_change_requests', $requestId, null, null, "Pengajuan ganti jabatan ditolak");
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Gagal menolak pengajuan.'];
    }
}

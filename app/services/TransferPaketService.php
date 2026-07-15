<?php
/**
 * Service TransferPaket — menangani logika bisnis pengajuan dan approval transfer paket
 */
class TransferPaketService
{
    private AuditService $auditService;
    private AssignmentTransfer $transferModel;
    private Paket $paketModel;
    private User $userModel;
    private EmailService $emailService;

    public function __construct(private mysqli $db)
    {
        $this->auditService = new AuditService($db);
        $this->transferModel = new AssignmentTransfer($db);
        $this->paketModel = new Paket($db);
        $this->userModel = new User($db);
        require_once BASEPATH . '/app/services/EmailService.php';
        $this->emailService = new EmailService();
    }

    public function ajukan(int $pengajuId, int $userTujuanId, string $tipeTransfer, string $alasan): array
    {
        // 1. User tujuan tidak boleh sama
        if ($pengajuId === $userTujuanId) {
            return ['ok' => false, 'message' => 'User tujuan tidak boleh sama dengan pengaju.'];
        }

        $userTujuan = $this->userModel->findById($userTujuanId);
        if (!$userTujuan || (int)$userTujuan['status_aktif'] !== 1) {
            return ['ok' => false, 'message' => 'User tujuan tidak valid atau belum aktif.'];
        }

        // 2. Cek pending double
        $pending = $this->transferModel->getPendingByUser($pengajuId);
        if ($pending) {
            return ['ok' => false, 'message' => 'Anda sudah memiliki pengajuan transfer yang sedang menunggu persetujuan.'];
        }

        // Insert ke DB
        $transferId = $this->transferModel->create([
            'dari_user_id' => $pengajuId,
            'ke_user_id' => $userTujuanId,
            'tipe_transfer' => $tipeTransfer,
            'alasan' => $alasan
        ]);

        if ($transferId) {
            $this->auditService->log(
                'TRANSFER', 'assignment_transfer', $transferId,
                null,
                ['ke_user_id' => $userTujuanId, 'tipe' => $tipeTransfer],
                'Mengajukan transfer total ke user ID ' . $userTujuanId
            );
            return ['ok' => true, 'message' => 'Pengajuan transfer massal berhasil dibuat dan menunggu persetujuan admin.'];
        }

        return ['ok' => false, 'message' => 'Gagal menyimpan pengajuan ke database.'];
    }

    public function setujui(int $transferId, int $adminId, ?string $catatan = null): array
    {
        $transfer = $this->transferModel->findById($transferId);
        if (!$transfer || $transfer['status'] !== 'menunggu') {
            return ['ok' => false, 'message' => 'Pengajuan tidak ditemukan atau bukan berstatus menunggu.'];
        }

        $tipe = $transfer['tipe_transfer'];
        $dariUserId = (int)$transfer['dari_user_id'];
        $keUserId = (int)$transfer['ke_user_id'];
        
        $userAsal = $this->userModel->findById($dariUserId);
        $userTujuan = $this->userModel->findById($keUserId);
        
        $roleAsal = $userAsal['jabatan_aktif'];
        $roleTujuan = $userTujuan['jabatan_aktif'];

        $this->db->begin_transaction();
        try {
            // Cek apakah terjadi Swap Role
            if ($roleAsal !== $roleTujuan && in_array($roleAsal, ['PPK', 'PP']) && in_array($roleTujuan, ['PPK', 'PP'])) {
                // 1. Update User Asal menjadi Role Tujuan
                $stmtA = $this->db->prepare("UPDATE users SET jabatan_aktif = ? WHERE id = ?");
                $stmtA->bind_param('si', $roleTujuan, $dariUserId);
                $stmtA->execute();
                
                // 2. Update User Tujuan menjadi Role Asal
                $stmtB = $this->db->prepare("UPDATE users SET jabatan_aktif = ? WHERE id = ?");
                $stmtB->bind_param('si', $roleAsal, $keUserId);
                $stmtB->execute();
                
                // Insert to user_role_history
                $stmt_hist = $this->db->prepare("INSERT INTO user_role_history (user_id, role_lama, role_baru, alasan, diubah_oleh) VALUES (?, ?, ?, ?, ?)");
                $alasanRole = "Swap otomatis dari persetujuan Transfer Jabatan & Paket (Transfer ID " . $transferId . ")";
                
                $stmt_hist->bind_param('isssi', $dariUserId, $roleAsal, $roleTujuan, $alasanRole, $adminId);
                $stmt_hist->execute();
                
                $stmt_hist->bind_param('isssi', $keUserId, $roleTujuan, $roleAsal, $alasanRole, $adminId);
                $stmt_hist->execute();
                $stmt_hist->close();

                // Audit Log Role Change Swap
                $this->auditService->log('ROLE_SWAP', 'users', $dariUserId, ['jabatan_aktif' => $roleAsal], ['jabatan_aktif' => $roleTujuan], "Swap jabatan dengan " . $userTujuan['nama']);
                $this->auditService->log('ROLE_SWAP', 'users', $keUserId, ['jabatan_aktif' => $roleTujuan], ['jabatan_aktif' => $roleAsal], "Swap jabatan dengan " . $userAsal['nama']);
            }
            
            // 3. Swap SEMUA paket PPK (Tanpa peduli role berubah atau tidak, swap semua paket milik mereka)
            $stmt_swap_ppk = $this->db->prepare("UPDATE paket SET ppk_id = CASE WHEN ppk_id = ? THEN ? WHEN ppk_id = ? THEN ? ELSE ppk_id END WHERE ppk_id IN (?, ?)");
            $stmt_swap_ppk->bind_param('iiiiii', $dariUserId, $keUserId, $keUserId, $dariUserId, $dariUserId, $keUserId);
            $stmt_swap_ppk->execute();
            $stmt_swap_ppk->close();
            
            // 4. Swap SEMUA paket PP
            $stmt_swap_pp = $this->db->prepare("UPDATE paket SET pp_id = CASE WHEN pp_id = ? THEN ? WHEN pp_id = ? THEN ? ELSE pp_id END WHERE pp_id IN (?, ?)");
            $stmt_swap_pp->bind_param('iiiiii', $dariUserId, $keUserId, $keUserId, $dariUserId, $dariUserId, $keUserId);
            $stmt_swap_pp->execute();
            $stmt_swap_pp->close();

            // Audit Log Transfer Paket Total
            $this->auditService->log('TRANSFER_TOTAL', 'paket', 0, null, null, "Seluruh paket milik " . $userAsal['nama'] . " ditukar dengan " . $userTujuan['nama']);

            // 5. Update assignment_transfer status
            $this->transferModel->approve($transferId, $adminId, $catatan);

            $this->db->commit();

            // 6. Kirim Email Notifikasi
            $roleSetelah = ($roleAsal !== $roleTujuan && in_array($roleAsal, ['PPK', 'PP']) && in_array($roleTujuan, ['PPK', 'PP'])) ? $roleAsal : $roleTujuan;
            $this->emailService->sendTransferNotificationToTarget($userTujuan['email'], $userTujuan['nama'], "Seluruh Paket (" . $userAsal['nama'] . ")", $roleSetelah);
            
            if ($userAsal) {
                $this->emailService->sendTransferNotificationToSender($userAsal['email'], $userAsal['nama'], "Seluruh Paket (" . $userTujuan['nama'] . ")", 'disetujui', $catatan ?? '');
            }

            return ['ok' => true, 'message' => 'Pengajuan transfer massal berhasil disetujui. Seluruh paket telah bertukar.'];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['ok' => false, 'message' => 'Terjadi kesalahan sistem saat memproses transfer: ' . $e->getMessage()];
        }
    }

    public function tolak(int $transferId, int $adminId, string $catatan): array
    {
        $transfer = $this->transferModel->findById($transferId);
        if (!$transfer || $transfer['status'] !== 'menunggu') {
            return ['ok' => false, 'message' => 'Pengajuan tidak ditemukan atau bukan berstatus menunggu.'];
        }

        if (empty(trim($catatan))) {
            return ['ok' => false, 'message' => 'Alasan penolakan wajib diisi.'];
        }

        $this->db->begin_transaction();
        try {
            $this->transferModel->reject($transferId, $adminId, $catatan);
            
            // Audit Log
            $this->auditService->log(
                'REJECT', 'assignment_transfer', $transferId,
                ['status' => 'menunggu'],
                ['status' => 'ditolak', 'catatan' => $catatan],
                "Transfer paket ditolak admin. Alasan: " . $catatan
            );

            $this->db->commit();

            // Email ke pengaju
            $userAsal = $this->userModel->findById((int)$transfer['dari_user_id']);
            if ($userAsal) {
                $this->emailService->sendTransferNotificationToSender($userAsal['email'], $userAsal['nama'], "Transfer Seluruh Paket", 'ditolak', $catatan);
            }

            return ['ok' => true, 'message' => 'Pengajuan transfer berhasil ditolak.'];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['ok' => false, 'message' => 'Terjadi kesalahan saat menolak pengajuan.'];
        }
    }
}

<?php
/**
 * Service Audit — menangani logging ke database
 */
class AuditService
{
    private AuditLog $auditModel;

    public function __construct(mysqli $db)
    {
        $this->auditModel = new AuditLog($db);
    }

    public function log(string $aksi, string $tabel, ?int $recordId, ?array $lama, ?array $baru, string $keterangan = ''): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['jabatan_aktif'] ?? 'system';

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $lamaJson = $lama ? json_encode($lama) : null;
        $baruJson = $baru ? json_encode($baru) : null;

        $this->auditModel->create([
            'user_id' => $userId,
            'role_saat_aksi' => $role,
            'tabel_terpengaruh' => $tabel,
            'record_id' => $recordId,
            'aksi' => $aksi,
            'detail_lama' => $lamaJson,
            'detail_baru' => $baruJson,
            'keterangan' => $keterangan,
            'ip_address' => $ip,
            'user_agent' => $ua
        ]);
    }
}

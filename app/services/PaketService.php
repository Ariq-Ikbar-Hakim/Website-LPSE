<?php
/**
 * Service Paket — logika bisnis paket
 */
class PaketService
{
    private Paket $paketModel;
    private AuditService $auditService;

    public function __construct(mysqli $db)
    {
        $this->paketModel = new Paket($db);
        $this->auditService = new AuditService($db);
    }

    public function createPaket(array $data): int|false
    {
        $id = $this->paketModel->create($data);
        if ($id) {
            $this->auditService->log('CREATE', 'paket', $id, null, $data, 'Pembuatan paket baru');
        }
        return $id;
    }

    public function submitToUkpbj(int $paketId, string $catatan): bool
    {
        $old = $this->paketModel->findById($paketId);
        if (!$old) return false;

        $ok = $this->paketModel->updateStatus($paketId, 'dikirim', $catatan);
        if ($ok) {
            $this->auditService->log('UPDATE', 'paket', $paketId, ['status' => $old['status']], ['status' => 'dikirim'], 'PPK mengirim usulan paket');
        }
        return $ok;
    }
    
    public function updateStatus(int $paketId, string $newStatus, string $catatan = ''): bool
    {
        $old = $this->paketModel->findById($paketId);
        if (!$old) return false;

        $ok = $this->paketModel->updateStatus($paketId, $newStatus, $catatan);
        if ($ok) {
            $this->auditService->log('UPDATE', 'paket', $paketId, ['status' => $old['status']], ['status' => $newStatus], 'Update status paket: ' . $catatan);
        }
        return $ok;
    }
}

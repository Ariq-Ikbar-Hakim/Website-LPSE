<?php
/**
 * KomentarController — menangani penambahan komentar & monitoring
 */
class KomentarController
{
    public function __construct(private mysqli $db) {}

    public function tambah(): void
    {
        AuthMiddleware::requireLogin();
        verifyCsrf();

        $paketId = (int)$_POST['paket_id'];
        $lampiranId = !empty($_POST['lampiran_id']) ? (int)$_POST['lampiran_id'] : null;
        $isiKomentar = trim($_POST['komentar']);

        if (empty($isiKomentar)) {
            flashSet('error', 'Komentar tidak boleh kosong.');
            redirect('index.php?page=paket_detail&id=' . $paketId);
        }

        $isMonitoring = (getRole() === 'admin') ? 1 : 0;

        $model = new Komentar($this->db);
        $model->create([
            'paket_id' => $paketId,
            'lampiran_id' => $lampiranId,
            'user_id' => $_SESSION['user_id'],
            'role_saat_komentar' => getRole(),
            'komentar' => $isiKomentar,
            'is_monitoring' => $isMonitoring
        ]);

        $msg = $isMonitoring ? 'Catatan monitoring berhasil ditambahkan.' : 'Komentar berhasil ditambahkan.';
        flashSet('success', $msg);
        redirect('index.php?page=paket_detail&id=' . $paketId);
    }
}

<?php
require_once 'config.php';
if(!isLogin()) redirect('login.php');
if(($_SESSION['hak_akses'] ?? '') === 'admin') redirect('index.php');

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buat_paket'){
    $tahun           = (int)$_POST['tahun_anggaran'];
    $kode_rup        = trim($_POST['kode_rup']);
    $nama            = trim($_POST['nama_paket']);
    $url             = trim($_POST['url_draft']);
    $hps             = (float)str_replace(['.','Rp',' '], '', $_POST['hps']);
    $pagu            = (float)str_replace(['.','Rp',' '], '', $_POST['pagu']);
    $sumber_dana     = $_POST['sumber_dana'];
    $jenis_pengadaan = $_POST['jenis_pengadaan'];
    $jenis_kontrak   = $_POST['jenis_kontrak'];
    $keterangan      = trim($_POST['keterangan'] ?? '');

    if(!$kode_rup || !$nama){
        // Untuk error yang lebih baik bisa disimpan ke $_SESSION flash message
        redirect($_SERVER['HTTP_REFERER']);
    } else {
        $stmt = $conn->prepare("INSERT INTO paket 
            (user_id, tahun_anggaran, kode_rup, nama_paket, url_draft_spse, hps, pagu,
             sumber_dana, jenis_pengadaan, jenis_kontrak, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssddssss",
            $_SESSION['user_id'], $tahun, $kode_rup, $nama, $url,
            $hps, $pagu, $sumber_dana, $jenis_pengadaan, $jenis_kontrak, $keterangan
        );
        if($stmt->execute()){
            $paket_id = $conn->insert_id;
            // Catat log
            $aksi = "PPK OPD » DRAF (buat paket dengan KODE PEKERJAAN '$kode_rup')";
            $stmt2 = $conn->prepare("INSERT INTO log_paket (paket_id, user_id, nama_pengguna, aksi, keterangan) VALUES (?,?,?,?,?)");
            $stmt2->bind_param("iisss", $paket_id, $_SESSION['user_id'], $_SESSION['nama'], $aksi, $keterangan);
            $stmt2->execute();
            redirect("paket_detail.php?id=$paket_id");
        } else {
            // Bisa tambahkan $_SESSION flash message untuk error jika diperlukan
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
} else {
    redirect('index.php');
}
?>

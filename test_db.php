<?php
require 'config.php';
$res = $conn->query("SELECT id, nama_paket, status, pagu, hps, tahun_anggaran, user_id FROM paket");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>

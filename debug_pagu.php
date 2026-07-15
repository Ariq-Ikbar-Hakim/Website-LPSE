<?php
require_once 'config.php';

echo "<h2>Debug Nilai Pagu</h2>";

// Cek semua paket dan nilai pagu-nya
$r = $conn->query("SELECT id, nama_paket, pagu, hps, status, user_id, tahun_anggaran FROM paket ORDER BY id DESC");
echo "<h3>Semua Paket di DB:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nama Paket</th><th>Pagu</th><th>HPS</th><th>Status</th><th>User ID</th><th>Tahun</th></tr>";
while($row = $r->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nama_paket']}</td>";
    echo "<td>" . ($row['pagu'] !== null ? number_format($row['pagu'],0,',','.') : '<b style=color:red>NULL</b>') . "</td>";
    echo "<td>" . ($row['hps']  !== null ? number_format($row['hps'],0,',','.')  : '<b style=color:red>NULL</b>') . "</td>";
    echo "<td>{$row['status']}</td>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td>{$row['tahun_anggaran']}</td>";
    echo "</tr>";
}
echo "</table>";

// Cek total pagu per user
echo "<h3>Total Pagu per User & Tahun:</h3>";
$r2 = $conn->query("SELECT user_id, tahun_anggaran, COUNT(*) AS total_paket, SUM(COALESCE(pagu,0)) AS total_pagu, SUM(COALESCE(hps,0)) AS total_hps FROM paket GROUP BY user_id, tahun_anggaran");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>User ID</th><th>Tahun</th><th>Total Paket</th><th>Total Pagu</th><th>Total HPS</th></tr>";
while($row = $r2->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td>{$row['tahun_anggaran']}</td>";
    echo "<td>{$row['total_paket']}</td>";
    echo "<td><b>" . number_format($row['total_pagu'],0,',','.') . "</b></td>";
    echo "<td><b>" . number_format($row['total_hps'],0,',','.') . "</b></td>";
    echo "</tr>";
}
echo "</table>";

// Cek session user yang login
echo "<h3>Session User Saat Ini:</h3>";
session_start();
echo "user_id = " . ($_SESSION['user_id'] ?? 'TIDAK ADA SESSION') . "<br>";
echo "nama = " . ($_SESSION['nama'] ?? '-') . "<br>";
?>

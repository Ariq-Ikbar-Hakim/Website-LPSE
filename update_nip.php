<?php
// Script update NIP admin - jalankan sekali lalu hapus
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'apelbaja_ppk';

$conn = new mysqli($host, $user, $pass, $db, 3306);
if($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// Cek semua user dengan hak_akses admin
$admins = [];
$r = $conn->query("SELECT id, nip, nama, hak_akses FROM users WHERE hak_akses='admin' ORDER BY id ASC");
while($row = $r->fetch_assoc()) $admins[] = $row;

echo "<pre>";
echo "=== DATA ADMIN DITEMUKAN ===\n";
foreach($admins as $a){
    echo "ID: {$a['id']} | NIP: {$a['nip']} | Nama: {$a['nama']}\n";
}
echo "\n";

if(count($admins) === 0){
    die("TIDAK ADA USER ADMIN! Cek hak_akses di tabel users.");
}

// Update admin pertama (ID terkecil) => NIP 230441100162
$conn->query("UPDATE users SET nip='230441100162' WHERE id={$admins[0]['id']}");
echo "Update ID {$admins[0]['id']} ({$admins[0]['nama']}): {$admins[0]['nip']} → 230441100162\n";
echo "Rows affected: " . $conn->affected_rows . "\n\n";

// Update admin kedua jika ada => NIP 230441100004
if(isset($admins[1])){
    $conn->query("UPDATE users SET nip='230441100004' WHERE id={$admins[1]['id']}");
    echo "Update ID {$admins[1]['id']} ({$admins[1]['nama']}): {$admins[1]['nip']} → 230441100004\n";
    echo "Rows affected: " . $conn->affected_rows . "\n\n";
}

// Verifikasi hasil
echo "=== VERIFIKASI SETELAH UPDATE ===\n";
$r2 = $conn->query("SELECT id, nip, nama, hak_akses FROM users WHERE hak_akses='admin' ORDER BY id");
while($row = $r2->fetch_assoc()){
    echo "ID: {$row['id']} | NIP BARU: {$row['nip']} | Nama: {$row['nama']}\n";
}
echo "\n✅ SELESAI! Hapus file ini setelah digunakan.\n";
echo "</pre>";

// Hapus file otomatis setelah 5 detik
echo "<script>setTimeout(()=>{window.location.href='index.php'},5000)</script>";
echo "<p><a href='index.php'>← Kembali ke Dashboard (auto 5 detik)</a></p>";
?>

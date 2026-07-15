<?php
// config.php
session_start();

$host = 'localhost';
$port = 3306;           // Port MySQL Anda
$db   = 'apelbaja_ppk';
$user = 'root';
$pass = '';             // kosongkan jika tidak ada password

// Koneksi dengan port
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi helper
function isLogin() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Set charset
$conn->set_charset("utf8mb4");
?>
<?php
define('BASEPATH', __DIR__);
require 'config/database.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($db->connect_error) die("Koneksi gagal: " . $db->connect_error);

$sql = "ALTER TABLE berita_acara ADD COLUMN file_laporan VARCHAR(255) NULL DEFAULT NULL AFTER status";
if ($db->query($sql) === TRUE) {
    echo "Kolom file_laporan berhasil ditambahkan.\n";
} else {
    echo "Error: " . $db->error . "\n";
}
$db->close();

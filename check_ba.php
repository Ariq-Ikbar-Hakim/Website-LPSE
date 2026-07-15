<?php
define('BASEPATH', __DIR__);
require 'config/database.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $db->query("SELECT id, status, file_laporan FROM berita_acara");
echo "Berita Acara Records:\n";
while($row = $res->fetch_assoc()){
    echo json_encode($row) . "\n";
}
$db->close();

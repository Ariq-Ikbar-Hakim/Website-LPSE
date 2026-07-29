<?php
require_once 'bootstrap.php';
$conn->query("ALTER TABLE berita_acara ADD COLUMN jenis_ba ENUM('otomatis', 'manual') NOT NULL DEFAULT 'otomatis' AFTER status");
echo "Migrasi jenis_ba berhasil.\n";

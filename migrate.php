<?php
define('BASEPATH', __DIR__);
require 'config/database.php';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check status column type
$res = $db->query("SHOW COLUMNS FROM paket LIKE 'status'");
$col = $res->fetch_assoc();
echo "Status column type: " . $col['Type'] . "\n";

if (strpos($col['Type'], 'enum') !== false) {
    // It's an ENUM. Let's modify it to include 'dibatalkan' if it doesn't already.
    if (strpos($col['Type'], 'dibatalkan') === false) {
        $newType = str_replace(")", ",'dibatalkan')", $col['Type']);
        $db->query("ALTER TABLE paket MODIFY status $newType DEFAULT 'draft'");
        echo "Updated status ENUM.\n";
    } else {
        echo "status ENUM already has 'dibatalkan'.\n";
    }
} else {
    echo "Status is not an ENUM.\n";
}

// Add dilihat_admin_at if not exists
$res = $db->query("SHOW COLUMNS FROM paket LIKE 'dilihat_admin_at'");
if ($res->num_rows == 0) {
    $db->query("ALTER TABLE paket ADD COLUMN dilihat_admin_at DATETIME NULL DEFAULT NULL AFTER status");
    echo "Added dilihat_admin_at column.\n";
} else {
    echo "dilihat_admin_at column already exists.\n";
}

// We also need signatures table if it's not created? It's used in Signature.php
// Let's verify if signatures table exists
$res = $db->query("SHOW TABLES LIKE 'signatures'");
if ($res->num_rows == 0) {
    echo "Table 'signatures' does not exist.\n";
    // I should create it just in case
} else {
    echo "Table 'signatures' exists.\n";
}

$db->close();
echo "Migration complete.\n";

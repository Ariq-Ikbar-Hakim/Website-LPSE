<?php
$title = "Monitoring Usulan Paket";
ob_start();
?>

<!-- Ini mirip dengan paket/index.php, tetapi dengan info tambahan untuk Admin -->
<!-- Include langsung view paket/index.php untuk reuse kode -->
<?php 
$status = 'semua';
$search = '';
$statusCounts = $paketModel->countByStatus(['exclude_status' => 'draft']);
require BASEPATH . '/views/paket/index.php'; 
?>

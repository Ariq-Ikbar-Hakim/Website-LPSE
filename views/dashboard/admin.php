<?php
$title = "Dashboard Admin";
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl p-6 shadow-lg text-white flex items-center justify-between">
        <div>
            <div class="text-sm text-indigo-200 font-medium mb-1">Total Paket (Tahun Ini)</div>
            <div class="text-4xl font-bold"><?= $totalPaket ?></div>
        </div>
        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-3xl">
            <i class="fas fa-chart-line"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-2">
            <div class="text-slate-500 font-medium text-sm">Nilai Pagu</div>
            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        <div class="text-2xl font-bold text-slate-800"><?= formatRupiah($totalPagu) ?></div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-2">
            <div class="text-slate-500 font-medium text-sm">Menunggu Konfirmasi</div>
            <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fas fa-user-clock"></i></div>
        </div>
        <div class="text-3xl font-bold text-slate-800"><?= $pendingUsersCount ?> <span class="text-sm font-normal text-slate-400">User</span></div>
        <?php if($pendingUsersCount > 0): ?>
            <a href="index.php?page=admin_konfirmasi" class="text-xs text-blue-600 font-medium mt-2 hover:underline">Tinjau sekarang &rarr;</a>
        <?php endif; ?>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-2">
            <div class="text-slate-500 font-medium text-sm">Paket Selesai</div>
            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="text-3xl font-bold text-slate-800"><?= $countsByStatus['selesai'] ?? 0 ?></div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-bold text-slate-800 text-lg">Monitoring Paket Berjalan</h3>
        <a href="index.php?page=admin_monitoring_usulan" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-semibold transition">Monitoring Detail</a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <?php
        $stats = [
            ['label' => 'Dikirim ke PP', 'count' => $countsByStatus['dikirim'] ?? 0, 'color' => 'blue'],
            ['label' => 'Sedang Dikaji', 'count' => $countsByStatus['kaji_ulang'] ?? 0, 'color' => 'amber'],
            ['label' => 'Perlu Revisi', 'count' => $countsByStatus['perlu_revisi'] ?? 0, 'color' => 'rose'],
            ['label' => 'Disetujui', 'count' => $countsByStatus['disetujui'] ?? 0, 'color' => 'emerald'],
            ['label' => 'Gagal', 'count' => $countsByStatus['gagal_pemilihan'] ?? 0, 'color' => 'slate']
        ];
        
        foreach ($stats as $st):
        ?>
        <div class="border border-slate-100 rounded-xl p-4 text-center hover:bg-slate-50 transition cursor-default">
            <div class="text-3xl font-bold text-<?= $st['color'] ?>-600 mb-1"><?= $st['count'] ?></div>
            <div class="text-xs text-slate-500 font-medium uppercase tracking-wider"><?= $st['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

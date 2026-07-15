<?php
$title = "Dashboard PPK";
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
            <i class="fas fa-boxes"></i>
        </div>
        <div>
            <div class="text-3xl font-bold text-slate-800"><?= $totalPaket ?></div>
            <div class="text-sm text-slate-500 font-medium">Total Paket Anda</div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-slate-50 text-slate-600 flex items-center justify-center text-2xl">
            <i class="fas fa-edit"></i>
        </div>
        <div>
            <div class="text-3xl font-bold text-slate-800"><?= $countsByStatus['draft'] ?? 0 ?></div>
            <div class="text-sm text-slate-500 font-medium">Draft Belum Dikirim</div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
            <i class="fas fa-check-double"></i>
        </div>
        <div>
            <div class="text-3xl font-bold text-slate-800"><?= $countsByStatus['selesai'] ?? 0 ?></div>
            <div class="text-sm text-slate-500 font-medium">Paket Selesai</div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-2xl">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <div class="text-3xl font-bold text-slate-800"><?= $countsByStatus['perlu_revisi'] ?? 0 ?></div>
            <div class="text-sm text-slate-500 font-medium">Perlu Revisi</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Aktivitas Paket Terbaru</h3>
                <a href="index.php?page=paket_index" class="text-sm text-blue-600 font-medium hover:text-blue-700">Lihat Semua</a>
            </div>
            <div class="p-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4 font-medium">Nama Paket</th>
                            <th class="px-6 py-4 font-medium">PP Ditugaskan</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($recentPaket)): ?>
                        <tr><td colspan="3" class="px-6 py-8 text-center text-slate-400">Belum ada data paket.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentPaket as $p): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <a href="index.php?page=paket_detail&id=<?= $p['id'] ?>" class="font-medium text-slate-800 hover:text-blue-600 block truncate max-w-xs">
                                        <?= e($p['nama_paket']) ?>
                                    </a>
                                    <div class="text-xs text-slate-500 mt-1"><?= e($p['kode_rup']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-600"><?= e($p['nama_pp']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        <?php
                                        if($p['status']=='draft') echo 'bg-slate-100 text-slate-600';
                                        elseif($p['status']=='selesai') echo 'bg-emerald-100 text-emerald-700';
                                        elseif($p['status']=='perlu_revisi') echo 'bg-rose-100 text-rose-700';
                                        elseif($p['status']=='dikirim') echo 'bg-blue-100 text-blue-700';
                                        elseif($p['status']=='dibatalkan') echo 'bg-slate-800 text-white';
                                        else echo 'bg-amber-100 text-amber-700';
                                        ?>
                                    ">
                                        <?= strtoupper(str_replace('_', ' ', $p['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div>
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
            <div class="absolute -right-6 -top-6 text-white/10 text-9xl"><i class="fas fa-plus-circle"></i></div>
            <div class="relative z-10">
                <h3 class="text-xl font-bold mb-2">Buat Usulan Baru</h3>
                <p class="text-blue-100 text-sm mb-6 leading-relaxed">Mulai proses pengadaan baru dengan membuat draf paket dan menugaskan Pejabat Pengadaan (PP).</p>
                <a href="index.php?page=paket_buat" class="inline-block bg-white text-blue-700 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition shadow-md">
                    <i class="fas fa-plus mr-2"></i> Buat Paket
                </a>
            </div>
        </div>

        <?php if ($needAction > 0): ?>
        <div class="mt-6 bg-rose-50 border border-rose-200 rounded-2xl p-5 flex items-start gap-4">
            <div class="text-rose-500 text-2xl mt-1"><i class="fas fa-bell"></i></div>
            <div>
                <h4 class="font-bold text-rose-800">Perhatian!</h4>
                <p class="text-sm text-rose-600 mt-1">Anda memiliki <b><?= $needAction ?> paket</b> yang perlu direvisi berdasarkan catatan dari PP.</p>
                <a href="index.php?page=paket_index&status=perlu_revisi" class="text-sm font-bold text-rose-700 mt-2 inline-block hover:underline">Lihat Paket</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

<?php
$title = "Dashboard Pejabat Pengadaan (PP)";
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
            <i class="fas fa-inbox"></i>
        </div>
        <div>
            <div class="text-3xl font-bold text-slate-800"><?= $countsByStatus['dikirim'] ?? 0 ?></div>
            <div class="text-sm text-slate-500 font-medium">Usulan Masuk (Perlu Kaji)</div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
            <i class="fas fa-search"></i>
        </div>
        <div>
            <div class="text-3xl font-bold text-slate-800"><?= $countsByStatus['kaji_ulang'] ?? 0 ?></div>
            <div class="text-sm text-slate-500 font-medium">Sedang Dikaji Ulang</div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition">
        <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <div>
            <div class="text-3xl font-bold text-slate-800"><?= $countsByStatus['disetujui'] ?? 0 ?></div>
            <div class="text-sm text-slate-500 font-medium">Disetujui PP</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-800">Paket Membutuhkan Tindakan Anda</h3>
    </div>
    <div class="p-0">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-4 font-medium">Nama Paket</th>
                    <th class="px-6 py-4 font-medium">PPK Pengusul</th>
                    <th class="px-6 py-4 font-medium">Nilai Pagu</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 text-center font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php 
                $needsAction = array_filter($recentPaket, fn($p) => in_array($p['status'], ['dikirim', 'kaji_ulang']));
                if (empty($needsAction)): 
                ?>
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400"><i class="fas fa-glass-cheers text-3xl mb-3 block opacity-50"></i>Bagus! Semua paket sudah Anda periksa.</td></tr>
                <?php else: ?>
                    <?php foreach ($needsAction as $p): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800 truncate max-w-xs" title="<?= e($p['nama_paket']) ?>"><?= e($p['nama_paket']) ?></div>
                            <div class="text-xs text-slate-500 mt-1"><?= e($p['kode_rup']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-slate-600"><?= e($p['nama_ppk']) ?></td>
                        <td class="px-6 py-4 font-medium text-slate-700"><?= formatRupiah($p['pagu']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                <?= $p['status'] == 'dikirim' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' ?>">
                                <?= strtoupper(str_replace('_', ' ', $p['status'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="index.php?page=paket_detail&id=<?= $p['id'] ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition" title="Kaji Ulang">
                                <i class="fas fa-search"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100 text-center bg-slate-50/50">
        <a href="index.php?page=paket_index" class="text-sm font-semibold text-blue-600 hover:underline">Lihat Seluruh Paket &rarr;</a>
    </div>
</div>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

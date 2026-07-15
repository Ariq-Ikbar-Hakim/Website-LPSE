<?php
$title = "Daftar Paket Pengadaan";
ob_start();
$role = getRole();
?>

<!-- Tab Navigasi Berdasarkan Status -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6 p-2 flex overflow-x-auto hide-scrollbar">
    <a href="index.php?page=paket_index" class="whitespace-nowrap px-5 py-2.5 rounded-xl font-medium text-sm transition-colors <?= $status === 'semua' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
        Semua Paket <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs <?= $status === 'semua' ? 'text-white' : 'bg-slate-200 text-slate-600' ?>"><?= array_sum($statusCounts) ?></span>
    </a>
    
    <?php if ($role === 'PPK'): ?>
    <a href="index.php?page=paket_index&status=draft" class="whitespace-nowrap px-5 py-2.5 rounded-xl font-medium text-sm transition-colors <?= $status === 'draft' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
        Draft <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs <?= $status === 'draft' ? 'text-white' : 'bg-slate-200 text-slate-600' ?>"><?= $statusCounts['draft'] ?? 0 ?></span>
    </a>
    <?php endif; ?>

    <a href="index.php?page=paket_index&status=dikirim" class="whitespace-nowrap px-5 py-2.5 rounded-xl font-medium text-sm transition-colors <?= $status === 'dikirim' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
        Dikirim ke PP <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs <?= $status === 'dikirim' ? 'text-white' : 'bg-slate-200 text-slate-600' ?>"><?= $statusCounts['dikirim'] ?? 0 ?></span>
    </a>
    
    <a href="index.php?page=paket_index&status=kaji_ulang" class="whitespace-nowrap px-5 py-2.5 rounded-xl font-medium text-sm transition-colors <?= $status === 'kaji_ulang' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
        Sedang Dikaji <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs <?= $status === 'kaji_ulang' ? 'text-white' : 'bg-slate-200 text-slate-600' ?>"><?= $statusCounts['kaji_ulang'] ?? 0 ?></span>
    </a>

    <a href="index.php?page=paket_index&status=perlu_revisi" class="whitespace-nowrap px-5 py-2.5 rounded-xl font-medium text-sm transition-colors <?= $status === 'perlu_revisi' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
        Perlu Revisi <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs <?= $status === 'perlu_revisi' ? 'text-white' : 'bg-slate-200 text-slate-600' ?>"><?= $statusCounts['perlu_revisi'] ?? 0 ?></span>
    </a>
    
    <a href="index.php?page=paket_index&status=disetujui" class="whitespace-nowrap px-5 py-2.5 rounded-xl font-medium text-sm transition-colors <?= $status === 'disetujui' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
        Disetujui <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs <?= $status === 'disetujui' ? 'text-white' : 'bg-slate-200 text-slate-600' ?>"><?= $statusCounts['disetujui'] ?? 0 ?></span>
    </a>

    <a href="index.php?page=paket_index&status=selesai" class="whitespace-nowrap px-5 py-2.5 rounded-xl font-medium text-sm transition-colors <?= $status === 'selesai' ? 'bg-slate-800 text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' ?>">
        Selesai <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-xs <?= $status === 'selesai' ? 'text-white' : 'bg-slate-200 text-slate-600' ?>"><?= $statusCounts['selesai'] ?? 0 ?></span>
    </a>
</div>

<!-- Header Aksi & Pencarian -->
<div class="flex flex-col lg:flex-row justify-between gap-4 mb-6">
    <div class="flex-1 max-w-2xl relative">
        <form action="index.php" method="GET" class="flex flex-col md:flex-row gap-3">
            <input type="hidden" name="page" value="paket_index">
            <?php if ($status !== 'semua') echo '<input type="hidden" name="status" value="'.$status.'">'; ?>
            
            <select name="bulan" class="px-3 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 text-sm shadow-sm text-slate-600">
                <option value="">Semua Bulan</option>
                <?php
                $bln_arr = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                $sel_bln = $_GET['bulan'] ?? '';
                foreach($bln_arr as $b_val => $b_nama) {
                    $sel = ($sel_bln == $b_val) ? 'selected' : '';
                    echo "<option value='$b_val' $sel>$b_nama</option>";
                }
                ?>
            </select>
            
            <select name="tahun" class="px-3 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 text-sm shadow-sm text-slate-600">
                <option value="">Semua Tahun</option>
                <?php
                $sel_thn = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
                for($y = date('Y'); $y >= 2023; $y--) {
                    $sel = ($sel_thn == $y) ? 'selected' : '';
                    echo "<option value='$y' $sel>$y</option>";
                }
                ?>
            </select>

            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Cari nama paket atau RUP..." 
                       class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-sm text-sm">
                <?php if (!empty($search)): ?>
                    <a href="index.php?page=paket_index<?= $status !== 'semua' ? '&status='.$status : '' ?>" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-medium shadow-sm transition text-sm whitespace-nowrap">Filter</button>
        </form>
    </div>
    
    <div class="flex gap-3">
        <?php if ($role === 'PPK'): ?>
        <a href="index.php?page=paket_buat" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-medium shadow-sm shadow-blue-200 transition flex items-center gap-2 text-sm">
            <i class="fas fa-plus"></i> Buat Usulan Baru
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Tabel Data -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-4 font-medium">No</th>
                    <th class="px-6 py-4 font-medium min-w-[250px]">Detail Paket</th>
                    <th class="px-6 py-4 font-medium">Nilai Pagu</th>
                    <th class="px-6 py-4 font-medium">Tahun / Sumber</th>
                    <th class="px-6 py-4 font-medium">PPK / PP</th>
                    <th class="px-6 py-4 font-medium text-center">Status</th>
                    <th class="px-6 py-4 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php if (empty($pakets)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-2xl mx-auto mb-3"><i class="fas fa-folder-open"></i></div>
                        <h4 class="font-medium text-slate-600 mb-1">Tidak ada data paket</h4>
                        <p class="text-slate-400 text-xs">Ubah filter pencarian atau buat paket baru.</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($pakets as $idx => $p): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-slate-500"><?= $idx + 1 ?></td>
                        <td class="px-6 py-4">
                            <a href="index.php?page=paket_detail&id=<?= $p['id'] ?>" class="font-bold text-slate-800 hover:text-blue-600 leading-tight block mb-1">
                                <?= e($p['nama_paket']) ?>
                            </a>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">RUP: <?= e($p['kode_rup']) ?></span>
                                <span class="text-slate-400">&bull;</span>
                                <span class="text-slate-500 truncate max-w-[150px]"><?= e($p['jenis_pengadaan']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-700">
                            <?= formatRupiah($p['pagu']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800"><?= $p['tahun_anggaran'] ?></div>
                            <div class="text-xs text-slate-500"><?= e($p['sumber_dana']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <div class="text-xs" title="PPK Pengusul"><i class="fas fa-user-tie text-blue-500 w-4"></i> <?= e($p['nama_ppk']) ?></div>
                                <div class="text-xs" title="Pejabat Pengadaan"><i class="fas fa-user text-emerald-500 w-4"></i> <?= e($p['nama_pp']) ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php
                            $stClass = 'bg-slate-100 text-slate-600';
                            $icon = 'fas fa-file-alt';
                            if ($p['status'] == 'dikirim') { $stClass = 'bg-blue-100 text-blue-700'; $icon = 'fas fa-paper-plane'; }
                            if ($p['status'] == 'kaji_ulang') { $stClass = 'bg-amber-100 text-amber-700'; $icon = 'fas fa-search'; }
                            if ($p['status'] == 'perlu_revisi') { $stClass = 'bg-rose-100 text-rose-700'; $icon = 'fas fa-exclamation-circle'; }
                            if ($p['status'] == 'disetujui') { $stClass = 'bg-emerald-100 text-emerald-700'; $icon = 'fas fa-check-circle'; }
                            if ($p['status'] == 'selesai') { $stClass = 'bg-emerald-600 text-white'; $icon = 'fas fa-check-double'; }
                            ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $stClass ?> whitespace-nowrap">
                                <i class="<?= $icon ?>"></i> <?= strtoupper(str_replace('_', ' ', $p['status'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="index.php?page=paket_detail&id=<?= $p['id'] ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm" title="Detail / Kelola">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

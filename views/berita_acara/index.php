<?php
$title = "Daftar Berita Acara";
ob_start();
?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden p-8">
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Daftar Berita Acara</h3>
            <p class="text-sm text-slate-500 mt-1">Dokumen BA yang telah selesai ditandatangani.</p>
        </div>
        
        <!-- Filter BA -->
        <form action="index.php" method="GET" class="flex gap-2">
            <input type="hidden" name="page" value="ba_index">
            <select name="bulan" class="px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 text-sm">
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
            
            <select name="tahun" class="px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500 text-sm">
                <option value="">Semua Tahun</option>
                <?php
                $sel_thn = $_GET['tahun'] ?? date('Y');
                for($y = date('Y'); $y >= 2023; $y--) {
                    $sel = ($sel_thn == $y) ? 'selected' : '';
                    echo "<option value='$y' $sel>$y</option>";
                }
                ?>
            </select>
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Filter</button>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Nomor BA</th>
                    <th class="px-4 py-3">Paket Pekerjaan</th>
                    <th class="px-4 py-3">Tanggal BA</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($listBa)): ?>
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        <i class="fas fa-folder-open text-3xl mb-2 text-slate-300"></i>
                        <p>Belum ada Berita Acara yang selesai.</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($listBa as $i => $ba): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3"><?= $i + 1 ?></td>
                        <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($ba['nomor_ba']) ?></td>
                        <td class="px-4 py-3">
                            <?= htmlspecialchars($ba['nama_paket']) ?>
                            <div class="text-xs text-slate-400">RUP: <?= htmlspecialchars($ba['kode_rup']) ?></div>
                        </td>
                        <td class="px-4 py-3"><?= date('d M Y', strtotime($ba['tanggal_ba'])) ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if (!empty($ba['file_laporan'])): ?>
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= htmlspecialchars($ba['file_laporan']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-semibold transition">
                                    <i class="fas fa-eye"></i> Lihat PDF
                                </a>
                                <a href="<?= htmlspecialchars($ba['file_laporan']) ?>" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg text-xs font-semibold transition">
                                    <i class="fas fa-download"></i> Unduh
                                </a>
                            </div>
                            <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Sedang diproses</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-6">
        <a href="index.php?page=paket_index" class="inline-block border border-slate-200 hover:bg-slate-50 text-slate-600 px-6 py-2.5 rounded-xl font-medium transition">
            Kembali ke Daftar Paket
        </a>
    </div>
</div>
<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

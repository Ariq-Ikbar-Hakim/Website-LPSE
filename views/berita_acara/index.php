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
        <!-- Actions and Filter -->
        <div class="flex flex-col sm:flex-row gap-3">
            <?php if (isset($role) && $role === 'PP'): ?>
            <button type="button" onclick="document.getElementById('modal-manual-ba').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah BA Manual
            </button>
            <?php endif; ?>
            
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
                            <div class="font-medium text-slate-800"><?= htmlspecialchars($ba['nama_paket']) ?></div>
                            <div class="text-xs text-slate-400 mb-2">RUP: <?= htmlspecialchars($ba['kode_rup']) ?></div>
                            
                            <div class="mt-2 text-xs border-t border-slate-100 pt-2 space-y-1">
                                <div><span class="font-semibold text-slate-500">PPK:</span> <?= htmlspecialchars($ba['nama_ppk']) ?> <span class="text-slate-400">(<?= htmlspecialchars($ba['opd_ppk']) ?>)</span></div>
                                <div><span class="font-semibold text-slate-500">PP:</span> <?= htmlspecialchars($ba['nama_pp']) ?> <span class="text-slate-400">(<?= htmlspecialchars($ba['opd_pp']) ?>)</span></div>
                            </div>
                            
                            <?php if (isset($ba['jenis_ba']) && $ba['jenis_ba'] === 'manual'): ?>
                            <div class="mt-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-semibold border border-blue-100">
                                    <i class="fas fa-upload"></i> Manual oleh PP: <?= htmlspecialchars($ba['nama_pp']) ?>
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="mt-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[10px] font-semibold border border-emerald-100">
                                    <i class="fas fa-qrcode"></i> Digital (Otomatis)
                                </span>
                            </div>
                            <?php endif; ?>
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

<?php if (isset($role) && $role === 'PP'): ?>
<div id="modal-manual-ba" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-fade-in-up">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg">Unggah Berita Acara Manual</h3>
            <button type="button" onclick="document.getElementById('modal-manual-ba').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="index.php?page=ba_manual_upload" method="POST" enctype="multipart/form-data" class="p-6 overflow-y-auto max-h-[80vh]">
            <?= csrfField() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Pilih PPK (Pengusul Paket)</label>
                    <select name="ppk_id" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">-- Pilih PPK --</option>
                        <?php if(!empty($activePPK)): ?>
                            <?php foreach($activePPK as $ppk): ?>
                                <option value="<?= $ppk['id'] ?>"><?= htmlspecialchars($ppk['nama']) ?> (<?= htmlspecialchars($ppk['opd']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Nama Paket Pekerjaan</label>
                        <input type="text" name="nama_paket" placeholder="Contoh: Pengadaan Laptop Dinas Tahun 2026" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Kode RUP</label>
                        <input type="text" name="kode_rup" placeholder="Bebas" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tahun Anggaran</label>
                        <input type="number" name="tahun_anggaran" value="<?= date('Y') ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Pagu (Rp)</label>
                        <input type="text" name="pagu" placeholder="150.000.000" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">HPS (Rp)</label>
                        <input type="text" name="hps" placeholder="0" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                </div>
                <hr class="border-slate-100 my-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nomor Berita Acara</label>
                    <input type="text" name="nomor_ba" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Berita Acara</label>
                    <input type="date" name="tanggal_ba" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                    <label class="block text-sm font-bold text-blue-900 mb-2">Unggah Tanda Tangan Anda (PP)</label>
                    <input type="file" name="signature_image" accept="image/png, image/jpeg" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer">
                    <p class="text-xs text-blue-700 mt-2">Format PNG/JPG.</p>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-manual-ba').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit" onclick="return confirm('Yakin ingin mengunggah Berita Acara manual ini? Paket akan diteruskan ke PPK.')" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">Simpan & Lanjutkan ke PPK</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

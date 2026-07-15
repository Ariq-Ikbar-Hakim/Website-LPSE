<?php
$title = "Detail Paket: " . e($paket['nama_paket']);
$tab = $_GET['tab'] ?? 'detail';
$role = getRole();
ob_start();
?>

<!-- Header Status Paket -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden">
    <div class="absolute right-0 top-0 w-64 h-full bg-gradient-to-l from-slate-50 to-transparent z-0"></div>
    <div class="relative z-10">
        <h2 class="text-2xl font-bold text-slate-800 mb-1"><?= e($paket['nama_paket']) ?></h2>
        <div class="flex items-center gap-3 text-sm text-slate-500 font-medium">
            <span class="bg-slate-100 px-2 py-0.5 rounded font-mono text-slate-700">RUP: <?= e($paket['kode_rup']) ?></span>
            <span>&bull;</span>
            <span>Rp <?= number_format($paket['pagu'], 0, ',', '.') ?></span>
            <span>&bull;</span>
            <span><?= e($paket['sumber_dana']) ?> <?= $paket['tahun_anggaran'] ?></span>
        </div>
    </div>
    <div class="relative z-10 text-right">
        <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Status Saat Ini</div>
        <?php
            $stClass = 'bg-slate-100 text-slate-600 border-slate-200';
            $stText = strtoupper(str_replace('_', ' ', $paket['status']));
            if ($paket['status'] == 'dikirim') $stClass = 'bg-blue-50 text-blue-700 border-blue-200';
            if ($paket['status'] == 'kaji_ulang') $stClass = 'bg-amber-50 text-amber-700 border-amber-200';
            if ($paket['status'] == 'perlu_revisi') $stClass = 'bg-rose-50 text-rose-700 border-rose-200';
            if ($paket['status'] == 'disetujui') $stClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            if ($paket['status'] == 'selesai') $stClass = 'bg-emerald-600 text-white border-emerald-700';
        ?>
        <div class="inline-block px-4 py-1.5 rounded-full border font-bold text-sm <?= $stClass ?> shadow-sm">
            <?= $stText ?>
        </div>
        <?php if ($paket['dilihat_admin_at']): ?>
        <div class="mt-2 text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded inline-block border border-purple-200">
            <i class="fas fa-eye mr-1"></i> Sudah dilihat Admin
        </div>
        <?php endif; ?>
        <?php if ($role === 'PPK' && in_array($paket['status'], ['draft', 'perlu_revisi'])): ?>
            <div class="mt-4">
                <form action="index.php?page=paket_kirim" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $paket['id'] ?>">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md transition">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Dokumen ke PP
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Navigasi Tabs -->
<div class="flex border-b border-slate-200 mb-6 sticky top-16 bg-[#f8fafc] z-10 pt-2">
    <a href="index.php?page=paket_detail&id=<?= $paket['id'] ?>&tab=detail" class="px-6 py-3 font-semibold text-sm transition-colors border-b-2 <?= $tab === 'detail' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
        <i class="fas fa-info-circle mr-2"></i>Informasi Paket
    </a>
    <a href="index.php?page=paket_detail&id=<?= $paket['id'] ?>&tab=lampiran" class="px-6 py-3 font-semibold text-sm transition-colors border-b-2 <?= $tab === 'lampiran' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
        <i class="fas fa-folder-open mr-2"></i>Dokumen Lampiran
    </a>
    <a href="index.php?page=paket_detail&id=<?= $paket['id'] ?>&tab=komentar" class="px-6 py-3 font-semibold text-sm transition-colors border-b-2 <?= $tab === 'komentar' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
        <i class="fas fa-comments mr-2"></i>Diskusi & Catatan
    </a>
    <?php if (in_array($paket['status'], ['disetujui', 'selesai'])): ?>
    <a href="index.php?page=paket_detail&id=<?= $paket['id'] ?>&tab=berita_acara" class="px-6 py-3 font-semibold text-sm transition-colors border-b-2 <?= $tab === 'berita_acara' ? 'border-amber-500 text-amber-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
        <i class="fas fa-file-signature mr-2"></i>Berita Acara
    </a>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
    <a href="index.php?page=paket_detail&id=<?= $paket['id'] ?>&tab=audit" class="px-6 py-3 font-semibold text-sm transition-colors border-b-2 <?= $tab === 'audit' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
        <i class="fas fa-history mr-2"></i>Audit Trail
    </a>
    <?php endif; ?>
</div>

<!-- Konten Tabs -->
<div class="mb-10">
    <?php if ($tab === 'detail'): ?>
        <!-- TAB: DETAIL -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Info SIRUP -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                        <i class="fas fa-database text-blue-600"></i>
                        <h3 class="font-bold text-slate-800">Data Terintegrasi SIRUP</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div>
                                <div class="text-slate-500 mb-1">Kode RUP</div>
                                <div class="font-semibold text-slate-800 bg-slate-50 px-3 py-1.5 rounded border border-slate-100 inline-block"><?= e($paket['kode_rup']) ?></div>
                            </div>
                            <div>
                                <div class="text-slate-500 mb-1">Nama Paket</div>
                                <div class="font-semibold text-slate-800"><?= e($paket['nama_paket']) ?></div>
                            </div>
                            <div>
                                <div class="text-slate-500 mb-1">Pagu Anggaran</div>
                                <div class="font-bold text-slate-800 text-base"><?= formatRupiah($paket['pagu']) ?></div>
                            </div>
                            <div>
                                <div class="text-slate-500 mb-1">Tahun Anggaran</div>
                                <div class="font-semibold text-slate-800"><?= $paket['tahun_anggaran'] ?></div>
                            </div>
                            <div>
                                <div class="text-slate-500 mb-1">Metode Pengadaan</div>
                                <div class="font-semibold text-slate-800"><?= e($paket['metode_pengadaan']) ?></div>
                            </div>
                            <div>
                                <div class="text-slate-500 mb-1">Sumber Dana</div>
                                <div class="font-semibold text-slate-800"><?= e($paket['sumber_dana']) ?></div>
                            </div>
                            <div class="col-span-2">
                                <div class="text-slate-500 mb-1">Keterangan Tambahan</div>
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 text-slate-700 min-h-[60px]">
                                    <?= nl2br(e($paket['keterangan'] ?: '-')) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Info Tim Pengadaan -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                        <i class="fas fa-users text-emerald-600"></i>
                        <h3 class="font-bold text-slate-800">Tim Pengadaan</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">PPK</div>
                            <div>
                                <div class="font-bold text-slate-800"><?= e($paket['nama_ppk']) ?></div>
                                <div class="text-xs text-slate-500 mb-1">Pengusul / Pejabat Pembuat Komitmen</div>
                                <div class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded inline-block"><?= e($paket['opd_ppk']) ?></div>
                            </div>
                        </div>
                        <hr class="border-slate-100">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">PP</div>
                            <div>
                                <div class="font-bold text-slate-800"><?= e($paket['nama_pp']) ?></div>
                                <div class="text-xs text-slate-500 mb-1">Pejabat Pengadaan</div>
                                <div class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded inline-block"><?= e($paket['opd_pp']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aksi Status -->
                <?php if ($role === 'PP' && in_array($paket['status'], ['dikirim', 'kaji_ulang', 'disetujui', 'ditandatangani_pp'])): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-blue-100 bg-blue-50">
                            <h3 class="font-bold text-blue-800">Aksi Pejabat Pengadaan</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <form action="index.php?page=paket_action" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $paket['id'] ?>">
                                <div class="mb-3">
                                    <textarea name="catatan" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500" placeholder="Catatan opsional..."></textarea>
                                </div>
                                <button type="submit" name="action" value="setujui" onclick="return confirm('Anda yakin menyetujui paket ini dan lanjut ke tahap tanda tangan?');" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold shadow-md transition mb-3">
                                    Setujui Dokumen (Lanjut)
                                </button>
                                <button type="submit" name="action" value="revisi" onclick="return confirm('Kembalikan paket ini ke PPK untuk direvisi?');" class="w-full bg-rose-100 hover:bg-rose-200 text-rose-700 py-3 rounded-xl font-bold transition border border-rose-200 mb-3">
                                    Minta Revisi PPK
                                </button>
                                <button type="submit" name="action" value="batalkan" onclick="return confirm('PERINGATAN: Membatalkan paket bersifat final. Lanjutkan?');" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 rounded-xl font-bold transition border border-slate-200 text-xs uppercase tracking-wider">
                                    Batalkan Paket
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php elseif ($tab === 'lampiran'): ?>
        <!-- TAB: LAMPIRAN & VERSIONING -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file-pdf text-rose-500"></i>
                    <h3 class="font-bold text-slate-800">Dokumen Lampiran Aktif</h3>
                </div>
                
                <?php if ($role === 'PPK' && in_array($paket['status'], ['draft', 'perlu_revisi'])): ?>
                <button onclick="document.getElementById('modalUpload').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                    <i class="fas fa-upload mr-1"></i> Upload Lampiran
                </button>
                <?php endif; ?>
            </div>
            
            <div class="p-0">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="px-6 py-4 font-medium">Tipe Dokumen</th>
                            <th class="px-6 py-4 font-medium">Nama File & Versi</th>
                            <th class="px-6 py-4 font-medium">Status Validasi</th>
                            <th class="px-6 py-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($lampiranHistory)): ?>
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">Belum ada lampiran diunggah.</td></tr>
                        <?php else: ?>
                            <?php foreach ($lampiranHistory as $l): ?>
                            <tr class="<?= $l['is_active'] ? 'hover:bg-slate-50' : 'bg-slate-50/50 opacity-75' ?>">
                                <td class="px-6 py-4 font-medium text-slate-800">
                                    <?= e($l['tipe_dokumen']) ?>
                                    <?php if (!$l['is_active']): ?>
                                    <div class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">File Riwayat Lama</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-file-alt text-slate-400"></i>
                                        <a href="<?= APP_URL . '/uploads/lampiran/' . e($l['nama_file']) ?>" target="_blank" class="text-blue-600 hover:underline cursor-pointer font-medium" title="<?= e($l['nama_asli']) ?>">
                                            <?= e($l['nama_file']) ?>
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        <span class="font-mono <?= $l['is_active'] ? 'bg-blue-100 text-blue-700 font-bold' : 'bg-slate-100 text-slate-500' ?> px-1.5 py-0.5 rounded">v<?= $l['versi'] ?></span> &bull; 
                                        <?= round($l['ukuran_file']/1024, 1) ?> KB &bull; 
                                        Oleh: <?= e($l['uploader_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $valClass = 'bg-amber-100 text-amber-700';
                                        if ($l['status_validasi'] == 'disetujui') $valClass = 'bg-emerald-100 text-emerald-700';
                                        if ($l['status_validasi'] == 'revisi') $valClass = 'bg-rose-100 text-rose-700';
                                    ?>
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?= $valClass ?>">
                                        <?= strtoupper($l['status_validasi']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= APP_URL . '/uploads/lampiran/' . e($l['nama_file']) ?>" download class="text-blue-500 hover:text-blue-700 p-2"><i class="fas fa-download"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($tab === 'komentar'): ?>
        <!-- TAB: KOMENTAR & CATATAN -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2"><i class="fas fa-comments text-blue-500"></i> Riwayat Catatan & Diskusi</h3>
            
            <div class="space-y-6 mb-8">
                <?php if (empty($komentar)): ?>
                    <div class="text-center text-slate-400 py-4 italic">Belum ada catatan.</div>
                <?php else: ?>
                    <?php foreach ($komentar as $k): ?>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0
                                <?= $k['role_saat_komentar']=='PPK' ? 'bg-blue-500' : ($k['role_saat_komentar']=='PP' ? 'bg-emerald-500' : 'bg-purple-500') ?>">
                                <?= substr($k['pengirim_nama'],0,1) ?>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl rounded-tl-none p-4 flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="font-bold text-slate-800 text-sm"><?= e($k['pengirim_nama']) ?></span>
                                        <span class="text-xs font-medium px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-500 ml-2"><?= $k['role_saat_komentar'] ?></span>
                                        <?php if ($k['is_monitoring']): ?>
                                            <span class="text-xs font-bold text-purple-600 ml-2"><i class="fas fa-eye"></i> Catatan Admin</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-slate-400"><?= formatTanggal($k['created_at'], 'd M Y H:i') ?></div>
                                </div>
                                <?php if ($k['lampiran_id']): ?>
                                    <div class="mb-2 text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded inline-block border border-blue-100">
                                        <i class="fas fa-paperclip mr-1"></i> Referensi: <?= e($k['tipe_dokumen']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="text-sm text-slate-700 whitespace-pre-line"><?= e($k['komentar']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Form Tambah Catatan -->
            <?php if ($paket['status'] !== 'draft'): ?>
            <form action="index.php?page=komentar_tambah" method="POST" class="border-t border-slate-100 pt-6">
                <?= csrfField() ?>
                <input type="hidden" name="paket_id" value="<?= $paket['id'] ?>">
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        <?= substr($_SESSION['nama'],0,1) ?>
                    </div>
                    <div class="flex-1 space-y-3">
                        <textarea name="komentar" required rows="3" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800 placeholder-slate-400 shadow-sm" placeholder="Tambahkan catatan atau instruksi perbaikan..."></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-md transition">Kirim Catatan</button>
                        </div>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div class="border-t border-slate-100 pt-6 text-center text-slate-400 text-sm">
                Fitur diskusi akan terbuka setelah dokumen dikirim ke Pejabat Pengadaan (PP).
            </div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'berita_acara' && in_array($paket['status'], ['disetujui', 'selesai'])): ?>
        <!-- TAB: BERITA ACARA DIGITAL -->
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-8 text-center max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                <i class="fas fa-file-signature"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Berita Acara Kaji Ulang</h3>
            <p class="text-sm text-slate-500 mb-8">Berita acara ini akan ditandatangani secara digital menggunakan QR Code oleh Pejabat Pengadaan (PP) terlebih dahulu, kemudian Pejabat Pembuat Komitmen (PPK).</p>

            <?php
            // Periksa status penandatanganan
            global $conn;
            $baModel = new BeritaAcara($conn);
            $sigModel = new Signature($conn);
            $ba = $baModel->findByPaketId($paket['id']);
            $hasSigned = false;
            $isComplete = ($paket['status'] === 'selesai');
            $canSign = false;
            
            if ($ba) {
                $hasSigned = $sigModel->hasSigned($ba['id'], $_SESSION['user_id']);
            }
            
            if ($role === 'PP' && !$isComplete && !$hasSigned) {
                $canSign = true; // PP selalu bisa TTD duluan jika belum
            } elseif ($role === 'PPK' && !$isComplete && !$hasSigned && $ba && $ba['status'] === 'ditandatangani_pp') {
                $canSign = true; // PPK TTD setelah PP
            }
            ?>

            <?php if ($isComplete): ?>
                <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 mb-4">
                    <i class="fas fa-check-circle mr-2"></i> Berita Acara telah selesai ditandatangani.
                </div>
                <?php if (!empty($ba['file_laporan'])): ?>
                <div class="mt-4">
                    <a href="<?= APP_URL . '/' . e($ba['file_laporan']) ?>" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition">
                        <i class="fas fa-file-pdf"></i> Lihat / Unduh Laporan Pengadaan
                    </a>
                </div>
                <?php endif; ?>
            <?php elseif ($canSign): ?>
                <form action="index.php?page=ba_sign" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="paket_id" value="<?= $paket['id'] ?>">
                    
                    <div class="text-left bg-amber-50 p-4 rounded-xl border border-amber-200">
                        <label class="block text-sm font-bold text-amber-900 mb-2">Unggah Tanda Tangan Anda</label>
                        <input type="file" name="signature_image" accept="image/png, image/jpeg" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 cursor-pointer">
                        <p class="text-xs text-amber-700 mt-2">Format: PNG/JPG. Gambar tanda tangan akan diproses menjadi QR Code otomatis.</p>
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menandatangani secara digital?');" class="w-full <?= $role === 'PPK' ? 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200' : 'bg-blue-500 hover:bg-blue-600 shadow-blue-200' ?> text-white px-8 py-3 rounded-xl font-bold shadow-lg transition">
                        <?= $role === 'PPK' ? '<i class="fas fa-check-double mr-2"></i>Selesai' : '<i class="fas fa-paper-plane mr-2"></i>Kirim' ?> (<?= $role ?>)
                    </button>
                </form>
            <?php else: ?>
                <div class="bg-slate-50 text-slate-600 border border-slate-200 rounded-xl p-4 mb-4">
                    <?php if ($hasSigned): ?>
                        <i class="fas fa-clock mr-2"></i> Anda sudah menandatangani. Menunggu pihak lainnya.
                    <?php elseif ($role === 'PPK'): ?>
                        <i class="fas fa-clock mr-2"></i> Menunggu Pejabat Pengadaan (PP) menandatangani terlebih dahulu.
                    <?php else: ?>
                        <i class="fas fa-info-circle mr-2"></i> Anda tidak memiliki akses untuk menandatangani saat ini.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'audit' && $role === 'admin'): ?>
        <!-- TAB: AUDIT TRAIL (Admin Only) -->
        <div class="bg-slate-900 rounded-2xl shadow-lg p-6">
            <h3 class="font-bold text-white mb-6 flex items-center gap-2"><i class="fas fa-terminal text-emerald-400"></i> System Audit Trail</h3>
            
            <div class="space-y-4">
                <?php if(empty($logs)): ?>
                    <div class="text-slate-500 text-sm font-mono">No logs found for this entity.</div>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                        <div class="bg-slate-800 rounded-lg p-4 font-mono text-xs border-l-2 border-emerald-500">
                            <div class="flex justify-between items-start mb-2">
                                <div class="text-emerald-400 font-bold">[<?= $log['created_at'] ?>] ACTION: <?= $log['aksi'] ?></div>
                                <div class="text-slate-400">IP: <?= $log['ip_address'] ?></div>
                            </div>
                            <div class="text-slate-300 mb-1">USER: <span class="text-white"><?= $log['user_nama'] ?> (<?= $log['role_saat_aksi'] ?>)</span></div>
                            <div class="text-slate-400">MESSAGE: <?= $log['keterangan'] ?></div>
                            <?php if($log['detail_baru']): ?>
                                <div class="mt-2 p-2 bg-slate-950 rounded text-slate-500 overflow-x-auto">
                                    CHANGES: <?= $log['detail_baru'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Upload Lampiran (Sembunyi) -->
<div id="modalUpload" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4 flex">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800">Upload Lampiran</h3>
            <button onclick="document.getElementById('modalUpload').classList.add('hidden')" class="text-slate-400 hover:text-rose-500"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form action="index.php?page=lampiran_upload" method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrfField() ?>
            <input type="hidden" name="paket_id" value="<?= $paket['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Dokumen</label>
                <select name="tipe_dokumen" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 text-sm">
                    <option value="Kerangka Acuan Kerja (KAK)">Kerangka Acuan Kerja (KAK)</option>
                    <option value="Spesifikasi Teknis">Spesifikasi Teknis</option>
                    <option value="HPS">Harga Perkiraan Sendiri (HPS)</option>
                    <option value="Rancangan Kontrak">Rancangan Kontrak</option>
                    <option value="Lainnya">Dokumen Lainnya</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">File Upload</label>
                <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx" class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 bg-slate-50">
                <p class="text-xs text-slate-400 mt-2">Format: PDF, Word, Excel. Maks 10MB.</p>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-md transition">Upload File</button>
        </form>
    </div>
</div>

<script>
function reviewLampiran(id, nama) {
    // Sederhana pake prompt untuk demo
    let cat = prompt("Review dokumen: " + nama + "\nKetik 'OK' untuk Setujui, atau tulis catatan revisi:");
    if(cat !== null) {
        if(cat.trim().toUpperCase() === 'OK') {
            alert("Demo: Form Submit Review Disetujui");
        } else if (cat.trim() !== '') {
            alert("Demo: Form Submit Review Revisi\nCatatan: " + cat);
        } else {
            alert("Harap isi catatan revisi.");
        }
    }
}
</script>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

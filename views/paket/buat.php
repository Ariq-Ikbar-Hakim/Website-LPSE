<?php
$title = "Buat Usulan Paket Baru";
ob_start();
?>

<div class="max-w-4xl mx-auto">
    <!-- Progress Bar -->
    <div class="mb-8 relative">
        <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-200 -translate-y-1/2 rounded-full z-0"></div>
        <div class="absolute top-1/2 left-0 w-1/3 h-1 bg-blue-600 -translate-y-1/2 rounded-full z-0"></div>
        
        <div class="relative z-10 flex justify-between">
            <div class="flex flex-col items-center gap-2">
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold shadow-md shadow-blue-200 ring-4 ring-white"><i class="fas fa-info-circle"></i></div>
                <div class="text-xs font-bold text-blue-800">Info Dasar</div>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 border-2 border-slate-200 flex items-center justify-center font-bold ring-4 ring-white"><i class="fas fa-file-upload"></i></div>
                <div class="text-xs font-semibold text-slate-400">Lampiran</div>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 border-2 border-slate-200 flex items-center justify-center font-bold ring-4 ring-white"><i class="fas fa-paper-plane"></i></div>
                <div class="text-xs font-semibold text-slate-400">Kirim</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-lg"><i class="fas fa-edit"></i></div>
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Informasi Dasar Paket</h3>
                <p class="text-sm text-slate-500">Isi detail paket pengadaan berdasarkan data dari SIRUP.</p>
            </div>
        </div>
        
        <form action="index.php?page=paket_buat" method="POST" class="p-8">
            <?= csrfField() ?>
            
            <!-- Integrasi SIRUP Manual -->
            <div class="mb-8 bg-blue-50 border border-blue-100 rounded-xl p-5">
                <h4 class="font-semibold text-blue-800 mb-3 text-sm uppercase tracking-wide">Tarik Data SIRUP</h4>
                <div class="flex gap-3">
                    <input type="text" id="sirup_kode" placeholder="Masukkan Kode RUP" class="flex-1 px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-sm text-sm">
                    <button type="button" id="btnTarikSirup" class="bg-slate-800 hover:bg-slate-700 text-white px-5 py-3 rounded-xl font-medium transition shadow-sm whitespace-nowrap text-sm">
                        <i class="fas fa-sync-alt mr-2"></i> Tarik Data
                    </button>
                </div>
                <p class="text-xs text-blue-600 mt-2"><i class="fas fa-info-circle mr-1"></i> (Demo) Masukkan kode sembarang, sistem akan mengisi form secara otomatis sebagai contoh.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kode RUP <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_rup" id="kode_rup" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800 placeholder-slate-400">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_paket" id="nama_paket" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800 placeholder-slate-400">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pagu Anggaran (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-medium">Rp</span>
                        <input type="number" name="pagu" id="pagu" required min="0" class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800 placeholder-slate-400">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Anggaran <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun_anggaran" id="tahun_anggaran" required value="<?= date('Y') ?>" min="2020" max="<?= date('Y')+1 ?>" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Metode Pengadaan</label>
                    <input type="text" name="metode_pengadaan" id="metode_pengadaan" placeholder="Contoh: E-Purchasing" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800 placeholder-slate-400">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sumber Dana</label>
                    <select name="sumber_dana" id="sumber_dana" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800">
                        <option value="APBD">APBD</option>
                        <option value="APBN">APBN</option>
                        <option value="BLUD">BLUD</option>
                        <option value="LAINNYA">Lainnya</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Pengadaan</label>
                    <select name="jenis_pengadaan" id="jenis_pengadaan" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800">
                        <option value="BARANG">Barang</option>
                        <option value="PEKERJAAN KONSTRUKSI">Pekerjaan Konstruksi</option>
                        <option value="JASA KONSULTANSI">Jasa Konsultansi</option>
                        <option value="JASA LAINNYA">Jasa Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Penugasan Pejabat Pengadaan (PP) <span class="text-red-500">*</span></label>
                    <select name="pp_id" required class="w-full px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm font-medium text-slate-800 cursor-pointer">
                        <option value="">-- Pilih Pejabat Pengadaan --</option>
                        <?php foreach ($listPP as $pp): ?>
                            <option value="<?= $pp['id'] ?>"><?= e($pp['nama']) ?> - <?= e($pp['opd']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Keterangan Tambahan</label>
                <textarea name="keterangan" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition text-sm text-slate-800 placeholder-slate-400" placeholder="Catatan operasional jika ada..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                <a href="index.php?page=paket_index" class="px-6 py-3 text-sm font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition flex items-center gap-2">
                    Simpan Draft & Lanjut Upload <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Mockup Tarik SIRUP
document.getElementById('btnTarikSirup').addEventListener('click', function() {
    const kode = document.getElementById('sirup_kode').value;
    if(!kode) return alert('Masukkan kode RUP dulu');
    
    // Animasikan loading
    const icon = this.querySelector('i');
    icon.classList.add('fa-spin');
    
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        
        // Mock data
        document.getElementById('kode_rup').value = kode;
        document.getElementById('nama_paket').value = 'Pengadaan Laptop Dinas Tahun ' + new Date().getFullYear();
        document.getElementById('pagu').value = '150000000';
        document.getElementById('metode_pengadaan').value = 'E-Purchasing';
        document.getElementById('jenis_pengadaan').value = 'BARANG';
        
        alert('Data berhasil ditarik dari SIRUP (Mock)');
        
        // Highlight input
        const inputs = ['kode_rup', 'nama_paket', 'pagu', 'metode_pengadaan'];
        inputs.forEach(id => {
            document.getElementById(id).classList.add('bg-emerald-50', 'border-emerald-300');
        });
    }, 800);
});
</script>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

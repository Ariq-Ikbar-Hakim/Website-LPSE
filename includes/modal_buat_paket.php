<!-- Modal Buat Paket Baru -->
<div id="modalBuatPaket" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col transform scale-95 transition-transform duration-300" id="modalBuatPaketContent">
        
        <!-- Header Modal -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 flex-shrink-0">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Buat Berkas Usulan Baru</h2>
                <p class="text-xs text-slate-400 mt-1">*Label warna <span class="text-red-500 font-bold">merah</span> diisi oleh sistem dari SIRUP</p>
            </div>
            <button type="button" onclick="closeModalBuatPaket()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Form Area (Scrollable) -->
        <div class="p-6 overflow-y-auto hide-scrollbar flex-1">
            <form action="buat_paket.php" method="POST" id="formBuatPaket">
                <input type="hidden" name="action" value="buat_paket">

                <!-- Row 1: Jenis Paket & Kode SIRUP -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Jenis Paket</label>
                        <input type="text" value="Paket SPSE" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-500 text-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Kode SIRUP</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" name="kode_rup" id="kode_rup" class="flex-1 px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition" placeholder="Masukkan Kode SIRUP" required>
                            <div class="flex gap-2">
                                <button type="button" onclick="cariSirup()" class="flex-1 sm:flex-none px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-sm font-medium transition whitespace-nowrap active:scale-95 shadow-sm">
                                    Cari SIRUP
                                </button>
                                <a href="https://sirup.lkpp.go.id" target="_blank" class="flex-1 sm:flex-none px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 rounded-2xl text-sm font-medium transition whitespace-nowrap active:scale-95 flex items-center justify-center">
                                    <i class="fas fa-external-link-alt mr-1.5 text-xs"></i> Buka
                                </a>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5"><i class="fas fa-info-circle mr-1"></i>Draft paket dapat ditarik ke APELBAJA H+1 setelah input SPSE</p>
                    </div>
                </div>

                <!-- Row 2: URL Draft Paket -->
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">URL Draft Paket</label>
                    <input type="url" name="url_draft" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition" placeholder="https://sirup.lkpp.go.id/sirup/home/penyediaDafttar?idSatker=...">
                </div>

                <!-- Row 3: Nama Pekerjaan -->
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Nama Pekerjaan</label>
                    <input type="text" name="nama_paket" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition" placeholder="Contoh: PENGADAAN LAYANAN INTERNET" required>
                </div>

                <!-- Row 4: Tahun, Pagu, HPS -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tahun Anggaran</label>
                        <input type="number" name="tahun_anggaran" value="<?= date('Y') ?>" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition" required>
                    </div>
                    <div class="relative">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Pagu</label>
                        <span class="absolute left-4 top-[38px] text-slate-400 text-sm font-medium">Rp</span>
                        <input type="text" name="pagu" id="pagu" class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition" placeholder="0" oninput="formatRupiah(this)">
                    </div>
                    <div class="relative">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">HPS</label>
                        <span class="absolute left-4 top-[38px] text-slate-400 text-sm font-medium">Rp</span>
                        <input type="text" name="hps" id="hps" class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition" placeholder="0" oninput="formatRupiah(this)" required>
                    </div>
                </div>

                <!-- Row 5: Sumber Dana, Jenis Pengadaan, Jenis Kontrak -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Sumber Dana</label>
                        <select name="sumber_dana" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition appearance-none bg-white" required>
                            <option value="APBD">APBD</option>
                            <option value="APBN">APBN</option>
                            <option value="HIBAH">HIBAH</option>
                            <option value="PHLN">PHLN</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Jenis Pengadaan</label>
                        <select name="jenis_pengadaan" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition appearance-none bg-white" required>
                            <option value="JASA LAINNYA">JASA LAINNYA</option>
                            <option value="BARANG">BARANG</option>
                            <option value="KONSTRUKSI">KONSTRUKSI</option>
                            <option value="KONSULTANSI">KONSULTANSI</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Jenis Kontrak</label>
                        <select name="jenis_kontrak" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition appearance-none bg-white" required>
                            <option value="HARGA SATUAN">HARGA SATUAN</option>
                            <option value="LUMPSUM">LUMPSUM</option>
                            <option value="KONTRAK PAYUNG">KONTRAK PAYUNG</option>
                            <option value="KONTRAK TAHUNAN">KONTRAK TAHUNAN</option>
                        </select>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition resize-none" placeholder="Contoh: Langganan internet diskominfo..."></textarea>
                </div>
            </form>
        </div>

        <!-- Footer Modal -->
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row justify-end gap-3 flex-shrink-0 bg-slate-50 rounded-b-3xl">
            <button type="button" onclick="closeModalBuatPaket()" class="w-full sm:w-auto px-6 py-2.5 border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition text-sm font-medium">
                Batal
            </button>
            <button type="button" onclick="submitBuatPaket()" id="btnSubmitPaket" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition active:scale-95 shadow-sm text-sm flex items-center justify-center">
                <i class="fas fa-save mr-2"></i> Simpan Paket
            </button>
        </div>

    </div>
</div>

<script>
function openModalBuatPaket() {
    const modal = document.getElementById('modalBuatPaket');
    const content = document.getElementById('modalBuatPaketContent');
    modal.classList.remove('hidden');
    // slight delay for animation
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }, 10);
    // Tutup sidebar jika sedang di mobile agar fokus ke modal
    if(typeof closeSidebar === 'function') closeSidebar();
}

function closeModalBuatPaket() {
    const modal = document.getElementById('modalBuatPaket');
    const content = document.getElementById('modalBuatPaketContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function submitBuatPaket() {
    const btn = document.getElementById('btnSubmitPaket');
    const form = document.getElementById('formBuatPaket');
    
    // validasi simple
    if(!document.getElementById('kode_rup').value || !document.getElementsByName('nama_paket')[0].value) {
        alert("Kode SIRUP dan Nama Pekerjaan wajib diisi!");
        return;
    }

    // Ubah state tombol ke loading
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Menyimpan...';
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    
    form.submit();
}

function formatRupiah(el){
    let v = el.value.replace(/\D/g,'');
    el.value = parseInt(v||0).toLocaleString('id-ID');
}

function cariSirup(){
    const kode = document.getElementById('kode_rup').value.trim();
    if(!kode){ alert('Masukkan kode SIRUP terlebih dahulu!'); return; }
    window.open('https://sirup.lkpp.go.id/sirup/ro/detailPaketPenyedia?kodeRup='+kode, '_blank');
}
</script>

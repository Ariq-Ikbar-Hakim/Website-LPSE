<?php
$title = "Riwayat Transfer Paket Saya";
ob_start();
?>

<div class="mb-6 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Transfer Paket Saya</h2>
        <p class="text-sm text-slate-500">Daftar pengajuan transfer paket di mana Anda bertindak sebagai pengaju atau penerima.</p>
    </div>
    <a href="index.php?page=transfer_ajukan" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> Ajukan Transfer
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Tanggal Pengajuan</th>
                    <th class="px-6 py-4">Jenis Pengajuan</th>
                    <th class="px-6 py-4">Dari User</th>
                    <th class="px-6 py-4">Ke User</th>
                    <th class="px-6 py-4">Tipe</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($riwayatTransfer)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada riwayat transfer paket.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayatTransfer as $t): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800"><?= date('d/m/Y', strtotime($t['created_at'])) ?></div>
                                <div class="text-xs text-slate-400"><?= date('H:i', strtotime($t['created_at'])) ?> WIB</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-700">Transfer Total & Jabatan</span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($t['dari_user_id'] == $_SESSION['user_id']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Anda
                                    </span>
                                <?php else: ?>
                                    <?= htmlspecialchars($t['nama_dari']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($t['ke_user_id'] == $_SESSION['user_id']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-green-100 text-green-700 border border-green-200">
                                        Anda
                                    </span>
                                <?php else: ?>
                                    <?= htmlspecialchars($t['nama_ke']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-md bg-indigo-50 text-indigo-600 border border-indigo-200 uppercase">
                                    <?= htmlspecialchars($t['tipe_transfer']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($t['status'] === 'menunggu'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div> Menunggu
                                    </span>
                                <?php elseif ($t['status'] === 'disetujui'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <i class="fas fa-check-circle"></i> Disetujui
                                    </span>
                                <?php elseif ($t['status'] === 'ditolak'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                                        <i class="fas fa-times-circle"></i> Ditolak
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="showDetail(<?= htmlspecialchars(json_encode($t)) ?>)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail -->
<div id="modalDetail" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl scale-95 transition-transform duration-300" id="modalDetailContent">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">Detail Pengajuan Transfer</h3>
            <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div class="col-span-2">
                    <p class="text-slate-500 mb-1">Jenis Pengajuan</p>
                    <p class="font-semibold text-slate-800">Transfer Total (Semua Paket & Jabatan)</p>
                </div>
                <div>
                    <p class="text-slate-500 mb-1">Tipe Transfer</p>
                    <p class="font-semibold text-slate-800 uppercase" id="dtl_tipe"></p>
                </div>
                <div>
                    <p class="text-slate-500 mb-1">Dari User</p>
                    <p class="font-semibold text-slate-800" id="dtl_dari"></p>
                </div>
                <div>
                    <p class="text-slate-500 mb-1">Ke User</p>
                    <p class="font-semibold text-slate-800" id="dtl_ke"></p>
                </div>
                <div class="col-span-2">
                    <p class="text-slate-500 mb-1">Alasan Pengajuan</p>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 text-slate-700" id="dtl_alasan"></div>
                </div>
                <div class="col-span-2" id="box_catatan_admin" style="display:none;">
                    <p class="text-slate-500 mb-1">Catatan Admin</p>
                    <div class="p-3 bg-red-50 rounded-lg border border-red-100 text-red-700" id="dtl_catatan"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-2xl flex justify-end">
            <button type="button" onclick="closeModal()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl transition">Tutup</button>
        </div>
    </div>
</div>

<script>
function showDetail(data) {
    document.getElementById('dtl_tipe').textContent = data.tipe_transfer;
    document.getElementById('dtl_dari').textContent = data.nama_dari;
    document.getElementById('dtl_ke').textContent = data.nama_ke;
    document.getElementById('dtl_alasan').textContent = data.alasan;
    
    if (data.status === 'ditolak' && data.catatan_admin) {
        document.getElementById('box_catatan_admin').style.display = 'block';
        document.getElementById('dtl_catatan').textContent = data.catatan_admin;
    } else if (data.status === 'disetujui' && data.catatan_admin) {
        document.getElementById('box_catatan_admin').style.display = 'block';
        document.getElementById('dtl_catatan').className = "p-3 bg-emerald-50 rounded-lg border border-emerald-100 text-emerald-700";
        document.getElementById('dtl_catatan').textContent = data.catatan_admin;
    } else {
        document.getElementById('box_catatan_admin').style.display = 'none';
    }

    const modal = document.getElementById('modalDetail');
    const content = document.getElementById('modalDetailContent');
    modal.classList.remove('hidden');
    // trigger reflow
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95');
}

function closeModal() {
    const modal = document.getElementById('modalDetail');
    const content = document.getElementById('modalDetailContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}
</script>

<?php
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

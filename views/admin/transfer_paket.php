<?php
$title = "Persetujuan Transfer Paket";
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Persetujuan Transfer Paket</h2>
    <p class="text-sm text-slate-500">Kelola pengajuan alih tanggung jawab paket (PP/PPK).</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Waktu Pengajuan</th>
                    <th class="px-6 py-4">Jenis Pengajuan</th>
                    <th class="px-6 py-4">Dari User</th>
                    <th class="px-6 py-4">Ke User</th>
                    <th class="px-6 py-4 text-center">Tipe Transfer</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($pendingTransfers)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">Tidak ada pengajuan transfer yang menunggu persetujuan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingTransfers as $t): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800"><?= date('d/m/Y', strtotime($t['created_at'])) ?></div>
                                <div class="text-xs text-slate-400"><?= date('H:i', strtotime($t['created_at'])) ?> WIB</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-700">
                                    Transfer Total & Jabatan
                                </span>
                                <div class="text-xs text-slate-500 mt-1 cursor-pointer underline hover:text-blue-600" onclick="showAlasan('<?= htmlspecialchars(addslashes($t['alasan'])) ?>')">Lihat Alasan</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800"><?= htmlspecialchars($t['nama_dari']) ?></div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Role saat ini: <?= htmlspecialchars($t['role_dari']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800"><?= htmlspecialchars($t['nama_ke']) ?></div>
                                <div class="text-[11px] <?= (strtoupper($t['role_ke']) === strtoupper($t['tipe_transfer'])) ? 'text-slate-500' : 'text-yellow-600 font-bold' ?> mt-0.5">
                                    Role saat ini: <?= htmlspecialchars($t['role_ke']) ?>
                                    <?php if (strtoupper($t['role_ke']) !== strtoupper($t['tipe_transfer'])): ?>
                                        <i class="fas fa-exclamation-triangle ml-1" title="Akan diubah otomatis jika disetujui"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-md bg-indigo-50 text-indigo-600 border border-indigo-200 uppercase">
                                    <?= htmlspecialchars($t['tipe_transfer']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button type="button" onclick="openApproveModal(<?= htmlspecialchars(json_encode($t)) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition mr-2" title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" onclick="openRejectModal(<?= htmlspecialchars(json_encode($t)) ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 transition" title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Alasan -->
<div id="modalAlasan" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl scale-95 transition-transform duration-300" id="modalAlasanContent">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Alasan Transfer</h3>
            <button onclick="closeAlasan()" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-5 text-slate-700 bg-slate-50 border-b border-slate-100" id="alasanContentBody">
        </div>
        <div class="p-5 flex justify-end">
            <button type="button" onclick="closeAlasan()" class="px-4 py-2 text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl transition">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div id="modalApprove" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl scale-95 transition-transform duration-300" id="modalApproveContent">
        <form action="index.php?page=admin_transfer_setujui" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="transfer_id" id="approve_transfer_id">
            
            <div class="p-6 border-b border-slate-100 flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xl">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Setujui Transfer Paket</h3>
                    <p class="text-sm text-slate-500 mt-1">Anda yakin ingin menyetujui pemindahan paket ini?</p>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Warning Role Change -->
                <div id="approveRoleWarning" class="hidden mb-4 p-4 rounded-xl bg-yellow-50 border border-yellow-200 flex gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                    <div class="text-sm text-yellow-800">
                        <b>Perhatian (Beda Jabatan):</b> Menyetujui pengajuan ini akan <b>menukar jabatan</b> kedua pengguna dan <b>menukar SELURUH paket</b> mereka secara total.
                    </div>
                </div>

                <div id="approveSameRoleWarning" class="hidden mb-4 p-4 rounded-xl bg-blue-50 border border-blue-200 flex gap-3">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="text-sm text-blue-800">
                        <b>Perhatian (Sesama Jabatan):</b> Menyetujui pengajuan ini akan <b>menukar SELURUH paket</b> mereka secara total (jabatan tidak berubah).
                    </div>
                </div>

                <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Admin (Opsional)</label>
                <textarea name="catatan_admin" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Misal: Sesuai disposisi pimpinan tanggal..."></textarea>
            </div>
            
            <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-2xl flex justify-end gap-3">
                <button type="button" onclick="closeApproveModal()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition">Ya, Setujui</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reject -->
<div id="modalReject" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl scale-95 transition-transform duration-300" id="modalRejectContent">
        <form action="index.php?page=admin_transfer_tolak" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="transfer_id" id="reject_transfer_id">
            
            <div class="p-6 border-b border-slate-100 flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 text-xl">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Tolak Transfer Paket</h3>
                    <p class="text-sm text-slate-500 mt-1">Pengajuan yang ditolak akan dikembalikan ke user pengaju.</p>
                </div>
            </div>
            
            <div class="p-6">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="catatan_admin" rows="3" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none transition-all" placeholder="Alasan mengapa pengajuan ini ditolak..."></textarea>
            </div>
            
            <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-2xl flex justify-end gap-3">
                <button type="button" onclick="closeRejectModal()" class="px-5 py-2.5 text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition">Tolak Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAlasan(text) {
    document.getElementById('alasanContentBody').textContent = text;
    const modal = document.getElementById('modalAlasan');
    const content = document.getElementById('modalAlasanContent');
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95');
}
function closeAlasan() {
    const modal = document.getElementById('modalAlasan');
    const content = document.getElementById('modalAlasanContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => { modal.classList.add('hidden'); }, 300);
}

function openApproveModal(data) {
    document.getElementById('approve_transfer_id').value = data.id;
    
    // Check role warning
    const roleTarget = data.tipe_transfer.toUpperCase();
    const roleCurrent = data.role_ke.toUpperCase();
    
    const warnBox = document.getElementById('approveRoleWarning');
    const sameWarnBox = document.getElementById('approveSameRoleWarning');
    
    if (roleCurrent !== roleTarget) {
        warnBox.classList.remove('hidden');
        sameWarnBox.classList.add('hidden');
    } else {
        warnBox.classList.add('hidden');
        sameWarnBox.classList.remove('hidden');
    }

    const modal = document.getElementById('modalApprove');
    const content = document.getElementById('modalApproveContent');
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95');
}
function closeApproveModal() {
    const modal = document.getElementById('modalApprove');
    const content = document.getElementById('modalApproveContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => { modal.classList.add('hidden'); }, 300);
}

function openRejectModal(data) {
    document.getElementById('reject_transfer_id').value = data.id;
    const modal = document.getElementById('modalReject');
    const content = document.getElementById('modalRejectContent');
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95');
}
function closeRejectModal() {
    const modal = document.getElementById('modalReject');
    const content = document.getElementById('modalRejectContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => { modal.classList.add('hidden'); }, 300);
}
</script>

<?php
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

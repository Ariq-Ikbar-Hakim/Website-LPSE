<?php
$title = "Verifikasi Akun Pengguna";
ob_start();
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
        <h3 class="font-bold text-slate-800">Menunggu Verifikasi (<?= count($users) ?>)</h3>
        <p class="text-sm text-slate-500 mt-1">Daftar pengguna yang baru mendaftar dan menunggu persetujuan admin.</p>
    </div>
    <div class="p-0">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-4 font-medium">Pengguna</th>
                    <th class="px-6 py-4 font-medium">NIP & Kontak</th>
                    <th class="px-6 py-4 font-medium">OPD</th>
                    <th class="px-6 py-4 font-medium">Jabatan</th>
                    <th class="px-6 py-4 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php if(empty($users)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-emerald-300 rounded-full flex items-center justify-center text-3xl mx-auto mb-3"><i class="fas fa-check"></i></div>
                        <h4 class="font-medium text-slate-600 mb-1">Semua beres!</h4>
                        <p class="text-slate-400 text-xs">Tidak ada pendaftaran baru yang menunggu verifikasi.</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= e($u['nama']) ?></div>
                            <div class="text-xs text-slate-500 mt-1"><?= formatTanggal($u['created_at'], 'd M Y H:i') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-xs inline-block mb-1"><?= e($u['nip']) ?></div>
                            <div class="text-xs text-slate-500"><?= e($u['email']) ?></div>
                            <div class="text-xs text-slate-500"><?= e($u['no_telp']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 max-w-[200px] truncate" title="<?= e($u['opd']) ?>">
                            <?= e($u['opd']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold <?= $u['jabatan_aktif']=='PPK'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700' ?>">
                                <?= $u['jabatan_aktif'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <form action="index.php?page=admin_konfirmasi" method="POST" class="flex gap-2 justify-center">
                                <?= csrfField() ?>
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="action" value="terima" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition" title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="submit" name="action" value="tolak" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition" title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
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

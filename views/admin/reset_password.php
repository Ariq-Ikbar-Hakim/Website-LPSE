<?php
$title = "Permintaan Reset Password";
ob_start();
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
        <h3 class="font-bold text-slate-800">Daftar Permintaan</h3>
    </div>
    <div class="p-0">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-4 font-medium">Pengguna</th>
                    <th class="px-6 py-4 font-medium">Waktu Permintaan</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php if(empty($requests)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-400">Tidak ada permintaan reset password.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= e($r['nama']) ?></div>
                            <div class="text-xs text-slate-500"><?= e($r['nip']) ?> &bull; <?= e($r['jabatan_aktif']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <?= formatTanggal($r['diminta_at'], 'd M Y H:i') ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $c = 'bg-slate-100 text-slate-600';
                            if($r['status'] == 'menunggu') $c = 'bg-amber-100 text-amber-700';
                            if($r['status'] == 'disetujui') $c = 'bg-blue-100 text-blue-700';
                            if($r['status'] == 'digunakan') $c = 'bg-emerald-100 text-emerald-700';
                            if($r['status'] == 'kadaluarsa') $c = 'bg-rose-100 text-rose-700';
                            ?>
                            <span class="px-2 py-1 rounded text-xs font-bold <?= $c ?>"><?= strtoupper($r['status']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($r['status'] == 'menunggu'): ?>
                            <form action="index.php?page=admin_reset_password" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                <button type="submit" onclick="return confirm('Kirim link reset password ke email user?');" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg font-medium transition text-xs">
                                    Setujui & Kirim Link
                                </button>
                            </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
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

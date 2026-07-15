<?php
$title = "Ajukan Transfer Paket";
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Ajukan Transfer Jabatan & Paket</h2>
    <p class="text-sm text-slate-500">Pindahkan HAK AKSES (Jabatan) beserta SELURUH PAKET yang Anda miliki kepada user lain secara bertukar (swap).</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden max-w-3xl">
    <div class="p-6">
        <form action="index.php?page=transfer_ajukan" method="POST" id="formTransfer">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <!-- Pemilihan Paket dihapus karena ini transfer massal (semua paket) -->

            <div class="mb-5">
                <label for="user_tujuan_id" class="block text-sm font-semibold text-slate-700 mb-1">Pilih User Tujuan</label>
                <select name="user_tujuan_id" id="user_tujuan_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    <option value="" data-role="">-- Pilih User --</option>
                    <?php foreach ($userTujuanList as $u): ?>
                        <option value="<?= $u['id'] ?>" data-role="<?= $u['jabatan_aktif'] ?>">
                            <?= htmlspecialchars($u['nama']) ?> - <?= htmlspecialchars($u['jabatan_aktif']) ?> (<?= htmlspecialchars($u['opd'] ?: 'OPD Tidak Diisi') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Warning Banner -->
            <div id="roleWarningBanner" class="hidden mb-5 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex gap-3 items-start">
                <div class="text-yellow-600 mt-0.5"><i class="fas fa-exclamation-triangle text-lg"></i></div>
                <div>
                    <h4 class="text-sm font-bold text-yellow-800">Perhatian: Pertukaran Jabatan & Seluruh Paket</h4>
                    <p class="text-xs text-yellow-700 mt-1">
                        User yang Anda pilih menjabat sebagai <b id="lblRoleSaatIni"></b>, sedangkan Anda <b id="lblRoleTarget"><?= strtoupper($roleSaatIni) ?></b>. 
                        Jika disetujui, Anda berdua akan <b>bertukar jabatan</b> dan <b>semua paket</b> milik Anda akan ditukar dengan semua paket milik user tersebut!
                    </p>
                </div>
            </div>

            <!-- Warning Banner for SAME role -->
            <div id="sameRoleWarningBanner" class="hidden mb-5 bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3 items-start">
                <div class="text-blue-600 mt-0.5"><i class="fas fa-info-circle text-lg"></i></div>
                <div>
                    <h4 class="text-sm font-bold text-blue-800">Perhatian: Pertukaran Seluruh Paket</h4>
                    <p class="text-xs text-blue-700 mt-1">
                        Anda dan pengguna tujuan memiliki jabatan yang sama (<?= strtoupper($roleSaatIni) ?>). 
                        Jika disetujui, jabatan Anda tidak akan berubah, namun <b>semua paket Anda akan ditukar</b> dengan semua paket miliknya secara total!
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <label for="alasan" class="block text-sm font-semibold text-slate-700 mb-1">Alasan Transfer</label>
                <textarea name="alasan" id="alasan" rows="3" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Tuliskan alasan mengapa paket ini ditransfer..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="index.php?page=paket_index" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">Batal</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Ajukan Transfer Total
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetRole = '<?= strtoupper($roleSaatIni) ?>';
    const selectUser = document.getElementById('user_tujuan_id');
    const warningBanner = document.getElementById('roleWarningBanner');
    const sameRoleWarningBanner = document.getElementById('sameRoleWarningBanner');
    const lblRoleSaatIni = document.getElementById('lblRoleSaatIni');

    selectUser.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const userRole = selectedOption.getAttribute('data-role');
        
        warningBanner.classList.add('hidden');
        sameRoleWarningBanner.classList.add('hidden');

        if (userRole) {
            if (userRole !== targetRole) {
                lblRoleSaatIni.textContent = userRole;
                warningBanner.classList.remove('hidden');
            } else {
                sameRoleWarningBanner.classList.remove('hidden');
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require BASEPATH . '/views/layouts/app.php';
?>

<?php
require_once 'config.php';
if(!isLogin()) redirect('login.php');

$success = '';
$error = '';

// Proses Upload SK Baru
if($_POST && isset($_POST['action']) && $_POST['action'] == 'buat_sk'){
    $nomor_sk   = trim($_POST['nomor_sk']);
    $opd        = trim($_POST['opd']);
    $hak_akses  = $_POST['hak_akses'];
    $mulai      = $_POST['mulai_tanggal'];
    $sampai     = $_POST['sampai_tanggal'];
    $keterangan = trim($_POST['keterangan'] ?? '');
    $sk_file    = '';

    if(isset($_FILES['upload_sk']) && $_FILES['upload_sk']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['upload_sk']['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, ['pdf','jpg','jpeg','png'])){
            $error = "Format file SK harus PDF/JPG/PNG!";
        } elseif($_FILES['upload_sk']['size'] > 5*1024*1024){
            $error = "File SK maksimal 5 MB!";
        } else {
            $sk_file = 'uploads/sk/'.time().'_'.$_SESSION['user_id'].'.'.$ext;
            move_uploaded_file($_FILES['upload_sk']['tmp_name'], $sk_file);
        }
    } else {
        $error = "File SK wajib diupload!";
    }

    if(!$error){
        $uid = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE users SET sk_nomor=?, opd=?, hak_akses=?, sk_mulai=?, sk_sampai=?, sk_file=?, keterangan=?, status_aktif=0 WHERE id=?");
        $stmt->bind_param("sssssssi", $nomor_sk, $opd, $hak_akses, $mulai, $sampai, $sk_file, $keterangan, $uid);
        if($stmt->execute()){
            $success = "SK berhasil diupload! Silakan hubungi admin untuk proses verifikasi.";
        } else {
            $error = "Gagal menyimpan SK: ".$conn->error;
        }
    }
}

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<?php include 'includes/header.php'; ?>

<div class="flex min-h-screen">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 lg:ml-72 bg-slate-50 flex flex-col min-h-screen">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 p-4 lg:p-8 page-content">
            <!-- Breadcrumb -->
            <div class="text-sm text-slate-400 mb-2">Master / SK OPD</div>
            <h1 class="text-2xl font-semibold text-slate-800 mb-6">SK OPD</h1>

            <?php if($success): ?>
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-700 px-5 py-4 rounded-2xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <span><?= $success ?></span>
            </div>
            <?php endif; ?>

            <?php if($error): ?>
            <div class="bg-red-50 border border-red-300 text-red-700 px-5 py-4 rounded-2xl mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <span><?= $error ?></span>
            </div>
            <?php endif; ?>

            <!-- Info -->
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-5 py-3 rounded-2xl mb-6 text-sm">
                <i class="fas fa-info-circle mr-2"></i>
                Silahkan upload SK untuk melanjutkan ke tahap selanjutnya. Setelah upload SK, silakan hubungi admin untuk proses verifikasi.
            </div>

            <!-- Tabel SK -->
            <div class="bg-white rounded-3xl shadow-sm overflow-hidden mb-8">
                <div class="flex justify-between items-center px-8 py-5 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-700">Data SK OPD</h2>
                    <button onclick="document.getElementById('modalSK').classList.remove('hidden')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl flex items-center gap-2 text-sm transition">
                        <i class="fas fa-plus"></i> Buat Baru
                    </button>
                </div>

                <table class="w-full">
                    <thead class="bg-slate-50 text-slate-600 text-sm">
                        <tr>
                            <th class="text-left px-6 py-4">NIP</th>
                            <th class="text-left px-6 py-4">Nama</th>
                            <th class="text-left px-6 py-4">Hak Akses</th>
                            <th class="text-left px-6 py-4">Nomor SK</th>
                            <th class="text-left px-6 py-4">Tahun Anggaran</th>
                            <th class="text-left px-6 py-4">OPD</th>
                            <th class="text-left px-6 py-4">Sub Unit OPD</th>
                            <th class="text-left px-6 py-4">Status</th>
                            <th class="text-left px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if($user): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($user['nip']) ?></td>
                            <td class="px-6 py-4 font-medium"><?= htmlspecialchars($user['nama']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                    <?= htmlspecialchars($user['hak_akses']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($user['sk_nomor'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-sm"><?= date('Y') ?></td>
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($user['opd'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($user['sub_unit_opd'] ?? '-') ?></td>
                            <td class="px-6 py-4">
                                <?php if($user['status_aktif']): ?>
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs">Aktif</span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs">Menunggu Verifikasi</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                <?php if($user['sk_file']): ?>
                                <a href="<?= $user['sk_file'] ?>" target="_blank"
                                   class="text-blue-600 hover:text-blue-800 text-sm"><i class="fas fa-file-pdf mr-1"></i>Lihat</a>
                                <?php endif; ?>
                                <button onclick="document.getElementById('modalSK').classList.remove('hidden')"
                                        class="text-amber-600 hover:text-amber-800 text-sm"><i class="fas fa-edit mr-1"></i>Edit</button>
                            </td>
                        </tr>
                        <?php else: ?>
                        <tr><td colspan="9" class="text-center py-12 text-slate-400">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

<!-- Modal Buat SK OPD -->
<div id="modalSK" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-8 py-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800">Buat Baru SK OPD</h3>
            <button onclick="document.getElementById('modalSK').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-8 space-y-5">
            <input type="hidden" name="action" value="buat_sk">

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1.5">Nomor SK</label>
                    <input type="text" name="nomor_sk" value="<?= htmlspecialchars($user['sk_nomor'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:border-blue-500 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1.5">OPD</label>
                    <input type="text" name="opd" value="<?= htmlspecialchars($user['opd'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:border-blue-500 text-sm" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1.5">Hak Akses</label>
                <select name="hak_akses" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:border-blue-500 text-sm" required>
                    <option value="PPK" <?= ($user['hak_akses']??'')=='PPK'?'selected':'' ?>>PPK - Pejabat Pembuat Komitmen</option>
                    <option value="PP" <?= ($user['hak_akses']??'')=='PP'?'selected':'' ?>>PP - Pejabat Pengadaan</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1.5">Mulai Tanggal SK</label>
                    <input type="date" name="mulai_tanggal" value="<?= $user['sk_mulai'] ?? '' ?>"
                           class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:border-blue-500 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1.5">Sampai Tanggal SK</label>
                    <input type="date" name="sampai_tanggal" value="<?= $user['sk_sampai'] ?? '' ?>"
                           class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:border-blue-500 text-sm" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1.5">Upload SK
                    <span class="text-slate-400 font-normal">(PDF/JPG/PNG, maks 5MB)</span>
                </label>
                <input type="file" name="upload_sk" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-4 py-3 border border-slate-300 rounded-2xl text-sm focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1.5">Keterangan</label>
                <textarea name="keterangan" rows="3"
                          class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:border-blue-500 text-sm resize-none"><?= htmlspecialchars($user['keterangan'] ?? '') ?></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalSK').classList.add('hidden')"
                        class="px-6 py-3 border border-slate-300 rounded-2xl text-sm hover:bg-slate-50 transition">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
                <button type="submit"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm transition font-medium">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'config.php';
if(!isLogin()) redirect('login.php');

// Hanya admin yang boleh akses
if(($_SESSION['hak_akses'] ?? '') !== 'admin'){
    redirect('index.php');
}

$success = '';
$error   = '';

// Aksi: Aktifkan akun
if($_POST && isset($_POST['action'])){
    $uid    = (int)$_POST['user_id'];
    $action = $_POST['action'];

    if($action === 'aktifkan'){
        $stmt = $conn->prepare("UPDATE users SET status_aktif=1 WHERE id=?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $success = "Akun berhasil diaktifkan!";
    } elseif($action === 'nonaktifkan'){
        $stmt = $conn->prepare("UPDATE users SET status_aktif=0 WHERE id=?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $success = "Akun berhasil dinonaktifkan.";
    } elseif($action === 'hapus'){
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $success = "Akun berhasil dihapus.";
    }
}

// Filter
$filter = $_GET['filter'] ?? 'semua';
$where  = '';
if($filter === 'pending')  $where = "WHERE status_aktif = 0";
if($filter === 'aktif')    $where = "WHERE status_aktif = 1";

$result = $conn->query("SELECT * FROM users $where ORDER BY status_aktif ASC, created_at DESC");
$users  = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Hitung badge pending
$cr_p   = $conn->query("SELECT COUNT(*) AS c FROM users WHERE status_aktif=0");
$pending = $cr_p ? $cr_p->fetch_assoc()['c'] : 0;
?>
<?php include 'includes/header.php'; ?>

<div class="flex min-h-screen">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 lg:ml-72 bg-slate-50 flex flex-col min-h-screen">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 p-4 lg:p-8 page-content">
            <!-- Breadcrumb -->
            <div class="text-sm text-slate-400 mb-2">Admin / Manajemen Pengguna</div>
            <h1 class="text-2xl font-semibold text-slate-800 mb-6">Konfirmasi Akun Pengguna</h1>

            <?php if($success): ?>
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-700 px-5 py-4 rounded-2xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Total Pengguna</p>
                        <p class="text-2xl font-bold text-slate-800"><?= count($users) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                        <i class="fas fa-clock text-amber-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Menunggu Konfirmasi</p>
                        <p class="text-2xl font-bold text-amber-600"><?= $pending ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <i class="fas fa-user-check text-emerald-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Akun Aktif</p>
                        <?php $aktif = $conn->query("SELECT COUNT(*) AS c FROM users WHERE status_aktif=1")->fetch_assoc()['c']; ?>
                        <p class="text-2xl font-bold text-emerald-600"><?= $aktif ?></p>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex gap-2 mb-4">
                <a href="?filter=semua"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition
                          <?= $filter==='semua' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?>">
                    Semua
                </a>
                <a href="?filter=pending"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition flex items-center gap-2
                          <?= $filter==='pending' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?>">
                    Pending
                    <?php if($pending > 0): ?>
                    <span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full"><?= $pending ?></span>
                    <?php endif; ?>
                </a>
                <a href="?filter=aktif"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition
                          <?= $filter==='aktif' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?>">
                    Aktif
                </a>
            </div>

            <!-- Tabel Users -->
            <div class="bg-white rounded-3xl shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-700">Daftar Pengguna</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 text-slate-600 text-sm">
                            <tr>
                                <th class="text-left px-6 py-4">NIP</th>
                                <th class="text-left px-6 py-4">Nama</th>
                                <th class="text-left px-6 py-4">Email</th>
                                <th class="text-left px-6 py-4">OPD</th>
                                <th class="text-left px-6 py-4">Hak Akses</th>
                                <th class="text-left px-6 py-4">Daftar</th>
                                <th class="text-left px-6 py-4">Status</th>
                                <th class="text-left px-6 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if($users): ?>
                            <?php foreach($users as $u): ?>
                            <tr class="hover:bg-slate-50 <?= !$u['status_aktif'] ? 'bg-amber-50/40' : '' ?>">
                                <td class="px-6 py-4 text-sm font-mono"><?= htmlspecialchars($u['nip']) ?></td>
                                <td class="px-6 py-4 font-medium text-slate-800"><?= htmlspecialchars($u['nama']) ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($u['opd'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        <?= $u['hak_akses']==='admin' ? 'bg-purple-100 text-purple-700' :
                                           ($u['hak_akses']==='PP'    ? 'bg-sky-100 text-sky-700' : 'bg-blue-100 text-blue-700') ?>">
                                        <?= htmlspecialchars($u['hak_akses']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400">
                                    <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($u['status_aktif']): ?>
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                                        <i class="fas fa-check mr-1"></i>Aktif
                                    </span>
                                    <?php else: ?>
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <?php if(!$u['status_aktif']): ?>
                                        <!-- Tombol Aktifkan -->
                                        <form method="POST" onsubmit="return confirm('Aktifkan akun <?= htmlspecialchars(addslashes($u['nama'])) ?>?')">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="action" value="aktifkan">
                                            <button type="submit"
                                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-medium transition flex items-center gap-1">
                                                <i class="fas fa-check"></i> Aktifkan
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <!-- Tombol Nonaktifkan -->
                                        <form method="POST" onsubmit="return confirm('Nonaktifkan akun <?= htmlspecialchars(addslashes($u['nama'])) ?>?')">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="action" value="nonaktifkan">
                                            <button type="submit"
                                                    class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-medium transition flex items-center gap-1">
                                                <i class="fas fa-ban"></i> Nonaktifkan
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                        <!-- Tombol SK jika ada -->
                                        <?php if(!empty($u['sk_file'])): ?>
                                        <a href="<?= htmlspecialchars($u['sk_file']) ?>" target="_blank"
                                           class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl text-xs font-medium transition flex items-center gap-1">
                                            <i class="fas fa-file-pdf"></i> SK
                                        </a>
                                        <?php endif; ?>

                                        <!-- Tombol Hapus -->
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" onsubmit="return confirm('HAPUS akun <?= htmlspecialchars(addslashes($u['nama'])) ?>? Tindakan ini tidak bisa dibatalkan!')">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="action" value="hapus">
                                            <button type="submit"
                                                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-medium transition flex items-center gap-1">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-16 text-slate-400">
                                    <i class="fas fa-users text-4xl mb-3 block opacity-30"></i>
                                    Tidak ada data pengguna
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

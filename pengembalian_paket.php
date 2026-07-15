<?php
require_once 'config.php';
if(!isLogin()) redirect('login.php');

$uid = (int)$_SESSION['user_id'];
$is_admin = ($_SESSION['hak_akses'] ?? '') === 'admin';

// Hitung per jenis untuk badge
$koreksi_per_jenis = [];
$jenis_list = ['BARANG','KONSULTANSI','KONSTRUKSI','JASA LAINNYA'];
if ($is_admin) {
    $rj = $conn->prepare("SELECT jenis_pengadaan, COUNT(*) AS c FROM paket WHERE status='koreksi' GROUP BY jenis_pengadaan");
} else {
    $rj = $conn->prepare("SELECT jenis_pengadaan, COUNT(*) AS c FROM paket WHERE user_id=? AND status='koreksi' GROUP BY jenis_pengadaan");
    $rj->bind_param("i", $uid);
}
$rj->execute();
$rjr = $rj->get_result();
while($row = $rjr->fetch_assoc()){
    $koreksi_per_jenis[$row['jenis_pengadaan']] = (int)$row['c'];
}
$total_koreksi = array_sum($koreksi_per_jenis);

$active_jenis = $_GET['jenis'] ?? 'BARANG';

// Ambil paket koreksi untuk jenis aktif
if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM paket WHERE status='koreksi' AND jenis_pengadaan=? ORDER BY updated_at DESC");
    $stmt->bind_param("s", $active_jenis);
} else {
    $stmt = $conn->prepare("SELECT * FROM paket WHERE user_id=? AND status='koreksi' AND jenis_pengadaan=? ORDER BY updated_at DESC");
    $stmt->bind_param("is", $uid, $active_jenis);
}
$stmt->execute();
$query = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="flex min-h-screen">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 lg:ml-72 bg-slate-50 flex flex-col min-h-screen">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 p-4 lg:p-8 page-content">

            <!-- Breadcrumb -->
            <div class="text-xs text-slate-400 mb-2 font-medium tracking-wider">
                <a href="index.php" class="hover:text-blue-600">HOME</a>
                <span class="mx-1">»</span> PENGEMBALIAN PAKET
            </div>

            <!-- Page header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-slate-800 flex items-center gap-3">
                        Pengembalian Paket
                        <?php if($total_koreksi > 0): ?>
                        <span class="badge-urgent text-sm bg-red-500 text-white px-2.5 py-0.5 rounded-full font-semibold"><?= $total_koreksi ?></span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-slate-500 text-sm mt-0.5">Paket yang dikembalikan dan memerlukan revisi</p>
                </div>
            </div>

            <!-- Info Banner -->
            <?php if($total_koreksi > 0): ?>
            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-5 py-4 rounded-2xl mb-6 flex items-start gap-3">
                <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-exclamation-triangle text-amber-500 text-sm"></i>
                </div>
                <div class="text-sm">
                    <strong>Paket Dikembalikan:</strong> Draft Paket dapat dikembalikan/tidak disetujui oleh Bagian Pengelolaan PBJ.
                    PPK dapat melakukan revisi terhadap dokumen yang dianggap salah atau tidak sesuai dengan ketentuan.
                </div>
            </div>
            <?php else: ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl mb-6 flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                </div>
                <div class="text-sm">
                    <strong>Semua paket sudah bersih!</strong> Tidak ada paket yang dikembalikan untuk dikoreksi saat ini.
                </div>
            </div>
            <?php endif; ?>

            <!-- ─── Main Card ─── -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                <!-- Jenis Tabs -->
                <div class="border-b border-slate-100 overflow-x-auto hide-scrollbar">
                    <div class="flex min-w-max px-2">
                        <?php foreach($jenis_list as $tj):
                            $cnt_tj = $koreksi_per_jenis[$tj] ?? 0;
                            $is_active = $active_jenis == $tj;
                        ?>
                        <a href="?jenis=<?= urlencode($tj) ?>"
                           class="flex items-center gap-2 px-5 py-4 text-xs font-semibold whitespace-nowrap border-b-2 transition
                                  <?= $is_active
                                      ? 'border-red-500 text-red-600'
                                      : 'border-transparent text-slate-500 hover:text-slate-700' ?>">
                            <?= $tj ?>
                            <?php if($cnt_tj > 0): ?>
                            <span class="<?= $is_active ? 'bg-red-500' : 'bg-red-300' ?> text-white text-[10px] px-1.5 py-0.5 rounded-full"><?= $cnt_tj ?></span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="p-4 lg:p-6">

                    <!-- Sub header -->
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="font-semibold text-slate-700">Daftar Paket — <span class="text-red-600">KOREKSI</span></h2>
                            <p class="text-xs text-slate-400 mt-0.5">Kategori: <?= htmlspecialchars($active_jenis) ?></p>
                        </div>
                        <?php if($query->num_rows > 0): ?>
                        <span class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-xl text-xs font-semibold">
                            <?= $query->num_rows ?> paket
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if($query->num_rows == 0): ?>
                    <div class="text-center py-14 text-slate-400">
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-emerald-400 text-2xl"></i>
                        </div>
                        <p class="font-medium text-slate-500">Tidak ada paket yang dikembalikan</p>
                        <p class="text-xs mt-1">untuk kategori <?= htmlspecialchars($active_jenis) ?></p>
                    </div>

                    <?php else: ?>

                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">#</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Kode SIRUP</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Nama Pekerjaan</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Tahun</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Pagu</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Sumber Dana</th>
                                    <th class="text-center px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                            <?php $i=1; $query->data_seek(0); while($row = $query->fetch_assoc()): ?>
                            <tr class="hover:bg-red-50/30 transition">
                                <td class="px-4 py-4 text-slate-400 text-xs"><?= $i++ ?></td>
                                <td class="px-4 py-4 font-mono text-blue-700 text-xs"><?= htmlspecialchars($row['kode_rup']) ?></td>
                                <td class="px-4 py-4 max-w-xs">
                                    <p class="font-medium text-slate-800 leading-tight"><?= htmlspecialchars($row['nama_paket']) ?></p>
                                    <?php if(!empty($row['catatan_koreksi'])): ?>
                                    <div class="flex items-start gap-1.5 mt-1.5 bg-red-50 rounded-lg px-2 py-1.5">
                                        <i class="fas fa-exclamation-circle text-red-400 text-xs mt-0.5 flex-shrink-0"></i>
                                        <p class="text-red-600 text-xs leading-tight"><?= htmlspecialchars(substr($row['catatan_koreksi'],0,100)) ?>...</p>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-slate-500"><?= htmlspecialchars($row['tahun_anggaran']) ?></td>
                                <td class="px-4 py-4 font-semibold text-slate-700">Rp <?= number_format($row['pagu']??0,0,',','.') ?></td>
                                <td class="px-4 py-4 text-slate-500"><?= htmlspecialchars($row['sumber_dana']??'APBD') ?></td>
                                <td class="px-4 py-4 text-center">
                                    <a href="paket_detail.php?id=<?= $row['id'] ?>&tab=log"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition">
                                        <i class="fas fa-edit text-[10px]"></i> Revisi
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden space-y-3">
                    <?php $query->data_seek(0); while($row = $query->fetch_assoc()): ?>
                    <div class="rounded-xl border border-red-200 bg-red-50/30 overflow-hidden">
                        <!-- Header -->
                        <div class="px-4 pt-4 pb-2">
                            <div class="flex items-start gap-2 mb-2">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-arrow-rotate-left text-red-500 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-800 text-sm leading-tight"><?= htmlspecialchars($row['nama_paket']) ?></p>
                                    <p class="text-xs text-blue-600 font-mono mt-0.5"><?= htmlspecialchars($row['kode_rup']) ?></p>
                                </div>
                            </div>
                            <?php if(!empty($row['catatan_koreksi'])): ?>
                            <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 flex items-start gap-2">
                                <i class="fas fa-exclamation-circle text-red-400 text-xs mt-0.5 flex-shrink-0"></i>
                                <p class="text-red-700 text-xs leading-tight"><?= htmlspecialchars(substr($row['catatan_koreksi'],0,120)) ?>...</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Info Grid -->
                        <div class="px-4 py-3 grid grid-cols-2 gap-3 bg-white border-t border-red-100">
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Pagu</p>
                                <p class="text-sm font-bold text-slate-700">Rp <?= number_format($row['pagu']??0,0,',','.') ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Tahun</p>
                                <p class="text-sm text-slate-700"><?= htmlspecialchars($row['tahun_anggaran']) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Sumber Dana</p>
                                <p class="text-sm text-slate-700"><?= htmlspecialchars($row['sumber_dana']??'APBD') ?></p>
                            </div>
                        </div>
                        <!-- Footer -->
                        <div class="px-4 py-3 flex justify-end border-t border-red-100">
                            <a href="paket_detail.php?id=<?= $row['id'] ?>&tab=log"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold transition">
                                <i class="fas fa-edit text-xs"></i> Lakukan Revisi
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    </div>

                    <?php endif; ?>

                </div>
            </div>

        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

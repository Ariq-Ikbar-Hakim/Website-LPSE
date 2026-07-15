<?php
require_once 'config.php';
if(!isLogin()) redirect('login.php');

$uid   = (int)$_SESSION['user_id'];
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$is_admin = ($_SESSION['hak_akses'] ?? '') === 'admin';

// ─── 2 Query GROUP BY (optimasi dari 12 query) ─────────────────────────────
$counts = [
    'draft'=>0,'dikirim'=>0,'kaji_ulang'=>0,'koreksi'=>0,
    'gagal_pemilihan'=>0,'distribusi'=>0,'penugasan'=>0,'pemilihan_selesai'=>0
];
if ($is_admin) {
    $r = $conn->prepare("SELECT status, COUNT(*) AS c FROM paket WHERE tahun_anggaran=? AND status != 'draft' GROUP BY status");
    $r->bind_param("i", $tahun);
} else {
    $r = $conn->prepare("SELECT status, COUNT(*) AS c FROM paket WHERE user_id=? AND tahun_anggaran=? GROUP BY status");
    $r->bind_param("ii", $uid, $tahun);
}
$r->execute();
$res_c = $r->get_result();
while($row = $res_c->fetch_assoc()){
    if(array_key_exists($row['status'], $counts)) $counts[$row['status']] = (int)$row['c'];
}

$jenis_list   = ['BARANG','KONSULTANSI','KONSTRUKSI','JASA LAINNYA'];
$jenis_counts = array_fill_keys($jenis_list, 0);
if ($is_admin) {
    $r2 = $conn->prepare("SELECT jenis_pengadaan, COUNT(*) AS c FROM paket WHERE tahun_anggaran=? AND status != 'draft' GROUP BY jenis_pengadaan");
    $r2->bind_param("i", $tahun);
} else {
    $r2 = $conn->prepare("SELECT jenis_pengadaan, COUNT(*) AS c FROM paket WHERE user_id=? AND tahun_anggaran=? GROUP BY jenis_pengadaan");
    $r2->bind_param("ii", $uid, $tahun);
}
$r2->execute();
$res_j = $r2->get_result();
while($row = $res_j->fetch_assoc()){
    if(in_array($row['jenis_pengadaan'], $jenis_list)) $jenis_counts[$row['jenis_pengadaan']] = (int)$row['c'];
}

// ─── Filter aktif ────────────────────────────────────────────────────────────
$active_tab   = $_GET['status'] ?? 'semua';
$active_jenis = $_GET['jenis'] ?? '';
$search       = trim($_GET['q'] ?? '');

if ($is_admin) {
    $where = "tahun_anggaran=? AND status != 'draft'";
    $params = [$tahun];
    $types  = "i";
} else {
    $where = "user_id=? AND tahun_anggaran=?";
    $params = [$uid, $tahun];
    $types  = "ii";
}

if($active_tab != 'semua'){ $where .= " AND status=?"; $params[] = $active_tab; $types .= "s"; }
if($active_jenis){          $where .= " AND jenis_pengadaan=?"; $params[] = $active_jenis; $types .= "s"; }
if($search){                $where .= " AND nama_paket LIKE ?"; $params[] = "%$search%"; $types .= "s"; }

$stmt = $conn->prepare("SELECT * FROM paket WHERE $where ORDER BY created_at DESC");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$query = $stmt->get_result();

$tab_labels = [
    'semua'             => ['label'=>'SEMUA',               'count'=>array_sum($counts), 'color'=>'bg-slate-500'],
    'draft'             => ['label'=>'DRAF',                 'count'=>$counts['draft'],   'color'=>'bg-slate-500'],
    'kaji_ulang'        => ['label'=>'KAJI ULANG',           'count'=>$counts['kaji_ulang'], 'color'=>'bg-amber-500'],
    'dikirim'           => ['label'=>'USULAN PAKET',         'count'=>$counts['dikirim'], 'color'=>'bg-blue-500'],
    'koreksi'           => ['label'=>'KOREKSI',              'count'=>$counts['koreksi'], 'color'=>'bg-red-500'],
    'gagal_pemilihan'   => ['label'=>'GAGAL PEMILIHAN',      'count'=>$counts['gagal_pemilihan'], 'color'=>'bg-rose-600'],
    'distribusi'        => ['label'=>'DISTRIBUSI',           'count'=>$counts['distribusi'], 'color'=>'bg-purple-500'],
    'penugasan'         => ['label'=>'PENUGASAN',            'count'=>$counts['penugasan'], 'color'=>'bg-indigo-500'],
    'pemilihan_selesai' => ['label'=>'PEMILIHAN SELESAI',    'count'=>$counts['pemilihan_selesai'], 'color'=>'bg-emerald-500'],
];
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
                <span class="mx-1">»</span> BERKAS USULAN
            </div>

            <!-- Top bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-slate-800">Berkas Usulan Paket</h1>
                    <p class="text-slate-500 text-sm mt-0.5">Kelola seluruh usulan paket pengadaan Anda</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($active_tab) ?>">
                        <select name="tahun" onchange="this.form.submit()"
                                class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <?php for($y=date('Y');$y>=2020;$y--): ?>
                            <option value="<?= $y ?>" <?= $tahun==$y?'selected':'' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                    <?php if(!$is_admin): ?>
                    <a href="javascript:void(0)" onclick="openModalBuatPaket()"
                       class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                        <i class="fas fa-plus text-xs"></i> Buat Paket
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ─── Main Card ─── -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                <!-- Status Tabs (scrollable) -->
                <div class="border-b border-slate-100 overflow-x-auto hide-scrollbar">
                    <div class="flex min-w-max px-2">
                        <?php foreach($tab_labels as $key => $tl): ?>
                        <a href="?tahun=<?= $tahun ?>&status=<?= $key ?>"
                           class="flex items-center gap-1.5 px-4 py-4 text-xs font-semibold whitespace-nowrap border-b-2 transition
                                  <?= $active_tab==$key
                                      ? 'border-blue-600 text-blue-600'
                                      : 'border-transparent text-slate-500 hover:text-slate-700' ?>">
                            <?= $tl['label'] ?>
                            <span class="px-1.5 py-0.5 rounded-full text-white text-[10px] min-w-[20px] text-center
                                         <?= $active_tab==$key ? 'bg-blue-600' : $tl['color'] ?>">
                                <?= $tl['count'] ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pengembalian sub-bar -->
                <div class="px-5 py-3 border-b border-slate-50 bg-slate-50/50 flex flex-wrap gap-2 items-center">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Pengembalian:</span>
                    <span class="px-3 py-1.5 border border-slate-200 bg-white rounded-xl text-xs text-slate-500 flex items-center gap-1.5 font-medium">
                        PAKET DIUMUMKAN
                        <span class="bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-full text-[10px]">0</span>
                    </span>
                    <span class="px-3 py-1.5 border border-emerald-200 bg-emerald-50 rounded-xl text-xs text-emerald-700 flex items-center gap-1.5 font-medium">
                        PEMILIHAN SELESAI
                        <span class="bg-emerald-500 text-white px-1.5 py-0.5 rounded-full text-[10px]"><?= $counts['pemilihan_selesai'] ?></span>
                    </span>
                </div>

                <div class="p-4 lg:p-6">

                    <!-- Jenis Paket Cards -->
                    <?php
                    $jenis_colors = [
                        'BARANG'       => ['from-blue-500 to-blue-700',   'fa-box'],
                        'KONSULTANSI'  => ['from-purple-500 to-purple-700','fa-briefcase'],
                        'KONSTRUKSI'   => ['from-orange-500 to-orange-700','fa-hard-hat'],
                        'JASA LAINNYA' => ['from-teal-500 to-teal-700',   'fa-concierge-bell'],
                    ];
                    ?>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                        <?php foreach($jenis_list as $jn):
                            [$grad, $icon] = $jenis_colors[$jn]; ?>
                        <a href="?tahun=<?= $tahun ?>&status=<?= $active_tab ?>&jenis=<?= urlencode($jn) ?>"
                           class="stat-card bg-gradient-to-br <?= $grad ?> text-white rounded-xl p-4 relative overflow-hidden
                                  <?= $active_jenis==$jn ? 'ring-2 ring-offset-2 ring-white shadow-lg' : '' ?>">
                            <div class="absolute right-0 top-0 w-16 h-16 bg-white/10 rounded-full -translate-y-6 translate-x-6"></div>
                            <i class="fas <?= $icon ?> text-white/60 text-sm mb-2 block"></i>
                            <p class="text-2xl font-bold"><?= $jenis_counts[$jn] ?></p>
                            <p class="text-xs font-medium text-white/80 mt-1 leading-tight"><?= $jn ?></p>
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Search bar -->
                    <form method="GET" class="mb-5 flex gap-2">
                        <input type="hidden" name="tahun" value="<?= $tahun ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($active_tab) ?>">
                        <?php if($active_jenis): ?>
                        <input type="hidden" name="jenis" value="<?= htmlspecialchars($active_jenis) ?>">
                        <?php endif; ?>
                        <div class="relative flex-1 max-w-sm">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Cari nama paket..."
                                   class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <button type="submit"
                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">
                            Cari
                        </button>
                        <?php if($search || $active_jenis): ?>
                        <a href="?tahun=<?= $tahun ?>&status=<?= $active_tab ?>"
                           class="px-4 py-2.5 border border-slate-300 hover:bg-slate-50 text-slate-600 rounded-xl text-sm transition">
                            Reset
                        </a>
                        <?php endif; ?>
                    </form>

                    <!-- ─── TABEL (Desktop) ─── -->
                    <?php
                    $status_badge = [
                        'draft'             => 'bg-slate-100 text-slate-600',
                        'dikirim'           => 'bg-blue-100 text-blue-700',
                        'kaji_ulang'        => 'bg-amber-100 text-amber-700',
                        'koreksi'           => 'bg-red-100 text-red-700',
                        'gagal_pemilihan'   => 'bg-rose-100 text-rose-700',
                        'distribusi'        => 'bg-purple-100 text-purple-700',
                        'penugasan'         => 'bg-indigo-100 text-indigo-700',
                        'pemilihan_selesai' => 'bg-emerald-100 text-emerald-700',
                    ];
                    ?>

                    <?php if(!$query || $query->num_rows == 0): ?>
                    <div class="text-center py-16 text-slate-400">
                        <i class="fas fa-folder-open text-5xl mb-4 block text-slate-200"></i>
                        <p class="text-sm font-medium text-slate-500">Tidak ada paket ditemukan</p>
                        <p class="text-xs mt-1">Coba ubah filter atau buat paket baru</p>
                        <?php if(!$is_admin): ?>
                        <a href="javascript:void(0)" onclick="openModalBuatPaket()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
                            <i class="fas fa-plus text-xs"></i> Buat Paket
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php else: ?>

                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Kode SIRUP</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Nama Pekerjaan</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Pagu</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Sumber Dana</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Jenis</th>
                                    <th class="text-left px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Status</th>
                                    <th class="text-center px-4 py-3.5 text-slate-500 font-semibold text-xs uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                            <?php $query->data_seek(0); while($row = $query->fetch_assoc()):
                                $sbadge = $status_badge[$row['status']] ?? 'bg-slate-100 text-slate-600'; ?>
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-4 py-4 font-mono text-blue-700 text-xs"><?= htmlspecialchars($row['kode_rup']) ?></td>
                                <td class="px-4 py-4 max-w-xs">
                                    <p class="font-medium text-slate-800 leading-tight"><?= htmlspecialchars($row['nama_paket']) ?></p>
                                    <p class="text-xs text-slate-400 mt-0.5">TA <?= $row['tahun_anggaran'] ?> · BAG. PENGELOLAAN</p>
                                </td>
                                <td class="px-4 py-4 font-semibold text-slate-700">Rp <?= number_format($row['pagu']??0,0,',','.') ?></td>
                                <td class="px-4 py-4 text-slate-500 text-sm"><?= htmlspecialchars($row['sumber_dana']??'APBD') ?></td>
                                <td class="px-4 py-4">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium">
                                        <?= htmlspecialchars($row['jenis_pengadaan']??'-') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-2.5 py-1 <?= $sbadge ?> rounded-lg text-xs font-semibold">
                                        <?= strtoupper(str_replace('_',' ',$row['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <a href="paket_detail.php?id=<?= $row['id'] ?>"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition">
                                        <i class="fas fa-eye text-[10px]"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden space-y-3">
                    <?php $query->data_seek(0); while($row = $query->fetch_assoc()):
                        $sbadge = $status_badge[$row['status']] ?? 'bg-slate-100 text-slate-600'; ?>
                    <div class="bg-slate-50 rounded-xl border border-slate-100 overflow-hidden">
                        <!-- Card Header -->
                        <div class="px-4 pt-4 pb-3 flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800 text-sm leading-tight"><?= htmlspecialchars($row['nama_paket']) ?></p>
                                <p class="text-xs text-blue-600 font-mono mt-1"><?= htmlspecialchars($row['kode_rup']) ?></p>
                            </div>
                            <span class="px-2.5 py-1 <?= $sbadge ?> rounded-lg text-[11px] font-semibold whitespace-nowrap flex-shrink-0">
                                <?= strtoupper(str_replace('_',' ',$row['status'])) ?>
                            </span>
                        </div>
                        <!-- Card Body -->
                        <div class="px-4 pb-4 grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Pagu</p>
                                <p class="text-sm font-bold text-slate-700 mt-0.5">Rp <?= number_format($row['pagu']??0,0,',','.') ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Sumber Dana</p>
                                <p class="text-sm text-slate-700 mt-0.5"><?= htmlspecialchars($row['sumber_dana']??'APBD') ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Jenis</p>
                                <p class="text-sm text-slate-700 mt-0.5"><?= htmlspecialchars($row['jenis_pengadaan']??'-') ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">Tahun</p>
                                <p class="text-sm text-slate-700 mt-0.5"><?= $row['tahun_anggaran'] ?></p>
                            </div>
                        </div>
                        <!-- Card Footer -->
                        <div class="px-4 py-3 bg-white border-t border-slate-100 flex justify-end">
                            <a href="paket_detail.php?id=<?= $row['id'] ?>"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition">
                                <i class="fas fa-eye text-[10px]"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    </div>

                    <!-- Pagination info -->
                    <div class="flex items-center justify-between mt-5 pt-4 border-t border-slate-100 text-xs text-slate-400">
                        <span><?= $query->num_rows ?> paket ditemukan</span>
                        <span>TA <?= $tahun ?> · <?= htmlspecialchars(strtoupper($active_tab)) ?></span>
                    </div>

                    <?php endif; ?>

                </div>
            </div><!-- /main card -->

        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>

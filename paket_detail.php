<?php
require_once 'config.php';
if(!isLogin()) redirect('login.php');

if(!isset($_GET['id'])) die("ID Paket tidak ditemukan");
$id = (int)$_GET['id'];

// Ambil data paket
$stmt = $conn->prepare("SELECT p.*, u.nama AS nama_user, u.opd FROM paket p JOIN users u ON p.user_id=u.id WHERE p.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0) die("Data paket tidak ditemukan");
$paket = $result->fetch_assoc();

// Proses Update Kode RUP
if($_POST && isset($_POST['action']) && $_POST['action']=='update_rup'){
    $new_rup = trim($_POST['kode_rup']);
    if($new_rup && $new_rup !== $paket['kode_rup']) {
        $old_rup = $paket['kode_rup'];
        $stmt_upd = $conn->prepare("UPDATE paket SET kode_rup=? WHERE id=?");
        $stmt_upd->bind_param("si", $new_rup, $id);
        if($stmt_upd->execute()){
            // Log aksi
            $aksi = "PPK: MENGUBAH KODE RUP";
            $ket = "Mengubah Kode RUP dari '$old_rup' menjadi '$new_rup'";
            $stmt_log = $conn->prepare("INSERT INTO log_paket (paket_id, user_id, nama_pengguna, aksi, keterangan) VALUES (?,?,?,?,?)");
            $stmt_log->bind_param("iisss", $id, $_SESSION['user_id'], $_SESSION['nama'], $aksi, $ket);
            $stmt_log->execute();
            
            // Refresh data paket setelah diupdate
            $paket['kode_rup'] = $new_rup;
            $success_rup = "Kode SIRUP berhasil diperbarui!";
        }
    }
}

// Proses Update Status oleh Admin
if($_POST && isset($_POST['action']) && $_POST['action']=='update_status_admin'){
    if(($_SESSION['hak_akses'] ?? '') === 'admin') {
        $new_status = $_POST['new_status'];
        $catatan = $_POST['catatan_admin'] ?? '';
        $stmt_upd = $conn->prepare("UPDATE paket SET status=? WHERE id=?");
        $stmt_upd->bind_param("si", $new_status, $id);
        if($stmt_upd->execute()){
            $aksi = "UKPBJ: MENGUBAH STATUS MENJADI " . strtoupper(str_replace('_', ' ', $new_status));
            $stmt_log = $conn->prepare("INSERT INTO log_paket (paket_id, user_id, nama_pengguna, aksi, keterangan) VALUES (?,?,?,?,?)");
            $stmt_log->bind_param("iisss", $id, $_SESSION['user_id'], $_SESSION['nama'], $aksi, $catatan);
            $stmt_log->execute();
            $paket['status'] = $new_status;
            $success_rup = "Status berhasil diupdate menjadi " . strtoupper(str_replace('_', ' ', $new_status));
        }
    }
}

// Proses Validasi Lampiran (Admin)
if($_POST && isset($_POST['action']) && $_POST['action']=='validasi_lampiran'){
    if(($_SESSION['hak_akses'] ?? '') === 'admin') {
        $lamp_id = (int)$_POST['lampiran_id'];
        $st_val = $_POST['status_validasi']; // 'valid' atau 'revisi'
        $catatan = trim($_POST['catatan_revisi'] ?? '');
        if($st_val === 'valid') $catatan = NULL;
        $stmt_val = $conn->prepare("UPDATE lampiran SET status_validasi=?, catatan_revisi=? WHERE id=?");
        $stmt_val->bind_param("ssi", $st_val, $catatan, $lamp_id);
        $stmt_val->execute();
    }
}

// Proses upload lampiran
if($_POST && isset($_POST['action']) && $_POST['action']=='upload_lampiran'){
    $uploaded = 0;
    $tipe_docs = $_POST['tipe_dokumen'] ?? [];
    if(!empty($_FILES['lampiran']['name'][0])){
        foreach($_FILES['lampiran']['name'] as $k => $fname){
            if($_FILES['lampiran']['error'][$k] == 0){
                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                $newname = 'uploads/lampiran/'.time().'_'.$k.'_'.$id.'.'.$ext;
                move_uploaded_file($_FILES['lampiran']['tmp_name'][$k], $newname);
                $tipe = $tipe_docs[$k] ?? 'Dokumen';
                $c = $conn->prepare("SELECT id, file_path FROM lampiran WHERE paket_id=? AND tipe_dokumen=?");
                $c->bind_param("is", $id, $tipe);
                $c->execute();
                $resc = $c->get_result();
                if($resc->num_rows > 0){
                    $row_l = $resc->fetch_assoc();
                    @unlink($row_l['file_path']); // hapus file lama
                    $up = $conn->prepare("UPDATE lampiran SET nama_file=?, file_path=?, status_validasi='menunggu', catatan_revisi=NULL, uploaded_at=NOW() WHERE id=?");
                    $up->bind_param("ssi", $fname, $newname, $row_l['id']);
                    $up->execute();
                } else {
                    $stmt2 = $conn->prepare("INSERT INTO lampiran (paket_id, nama_dokumen, nama_file, file_path, tipe_dokumen, status_validasi) VALUES (?,?,?,?,?,'menunggu')");
                    $stmt2->bind_param("issss", $id, $tipe, $fname, $newname, $tipe);
                    $stmt2->execute();
                }
                $uploaded++;
            }
        }
        if($uploaded) $success_lamp = "$uploaded file berhasil diupload.";
    }
}

// Proses Approval / Kirim ke UKPBJ
if($_POST && isset($_POST['action']) && $_POST['action']=='kirim_ukpbj'){
    $catatan = trim($_POST['catatan'] ?? '');
    $tindak = $_POST['tindak_lanjut'] ?? '';
    $new_status = 'dikirim';
    if($tindak == 'revisi') $new_status = 'dikirim';
    
    $stmt2 = $conn->prepare("UPDATE paket SET status=?, catatan_koreksi=? WHERE id=?");
    $stmt2->bind_param("ssi", $new_status, $catatan, $id);
    $stmt2->execute();

    // Log
    $aksi = "PPK: USULAN PAKET » DIKIRIM KE UKPBJ";
    $stmt3 = $conn->prepare("INSERT INTO log_paket (paket_id, user_id, nama_pengguna, aksi, keterangan) VALUES (?,?,?,?,?)");
    $stmt3->bind_param("iisss", $id, $_SESSION['user_id'], $_SESSION['nama'], $aksi, $catatan);
    $stmt3->execute();

    // Upload lampiran approval jika ada
    if(isset($_FILES['lampiran_kirim']) && $_FILES['lampiran_kirim']['error']==0){
        $ext = strtolower(pathinfo($_FILES['lampiran_kirim']['name'], PATHINFO_EXTENSION));
        $fn = 'uploads/lampiran/kirim_'.time().'_'.$id.'.'.$ext;
        move_uploaded_file($_FILES['lampiran_kirim']['tmp_name'], $fn);
    }

    // Refresh data
    $stmt = $conn->prepare("SELECT p.*, u.nama AS nama_user, u.opd FROM paket p JOIN users u ON p.user_id=u.id WHERE p.id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $paket = $stmt->get_result()->fetch_assoc();
    $success_kirim = "Paket berhasil dikirim ke UKPBJ Prov. Jatim!";
}

// Ambil lampiran
$lamps = $conn->query("SELECT * FROM lampiran WHERE paket_id=$id ORDER BY uploaded_at ASC");

// Ambil log
$logs = $conn->query("SELECT l.*, u.nama AS nm FROM log_paket l LEFT JOIN users u ON l.user_id=u.id WHERE l.paket_id=$id ORDER BY l.created_at DESC");

$active_tab = $_GET['tab'] ?? 'detail';

$status_labels = [
    'draft'             => ['label'=>'DRAFT',              'class'=>'bg-slate-100 text-slate-600'],
    'dikirim'           => ['label'=>'USULAN PAKET',        'class'=>'bg-blue-100 text-blue-700'],
    'kaji_ulang'        => ['label'=>'KAJI ULANG',          'class'=>'bg-amber-100 text-amber-700'],
    'koreksi'           => ['label'=>'KOREKSI',             'class'=>'bg-red-100 text-red-700'],
    'gagal_pemilihan'   => ['label'=>'GAGAL PEMILIHAN',     'class'=>'bg-red-200 text-red-800'],
    'distribusi'        => ['label'=>'DISTRIBUSI',          'class'=>'bg-purple-100 text-purple-700'],
    'penugasan'         => ['label'=>'PENUGASAN',           'class'=>'bg-indigo-100 text-indigo-700'],
    'pemilihan_selesai' => ['label'=>'PEMILIHAN SELESAI',   'class'=>'bg-emerald-100 text-emerald-700'],
];
$st = $status_labels[$paket['status']] ?? ['label'=>ucfirst($paket['status']),'class'=>'bg-slate-100 text-slate-600'];

// Jenis dokumen lampiran sesuai PDF
$jenis_dokumen = [
    'Surat Permintaan Pemilihan Penyedia',
    'SK Penetapan PPK',
    'KAK / Spesifikasi Teknis',
    'Dokumen Anggaran Belanja (DIPA/DPA)',
    'Dokumen Anggaran Belanja (DIPA/DPA Img)',
    'Spesifikasi Teknis (file word, pdf, image)',
    'Rencana Anggaran Biaya (RAB)',
    'Rencana Anggaran Biaya Kontrak (file word, pdf, image)',
    'Rancangan Kontrak (file word, pdf, no excel)',
    'Surat Izin/Izin Usaha (file word, pdf, no excel)',
    'Rencana HPS (file word, pdf)',
    'Syarat Kualifikasi (file word, pdf, no excel)',
    'Dokumen pendukung lain',
];

$is_gagal = $paket['status'] === 'gagal_pemilihan';
?>
<?php include 'includes/header.php'; ?>

<div class="flex min-h-screen">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 lg:ml-72 bg-slate-50 flex flex-col min-h-screen">
        <?php include 'includes/navbar.php'; ?>

        <div class="flex-1 p-4 lg:p-8 page-content">
            
            <?php if(isset($success_rup)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 px-4 py-3 rounded-2xl mb-6 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <?= $success_rup ?>
            </div>
            <?php endif; ?>

            <!-- Breadcrumb -->
            <div class="text-xs text-slate-400 mb-3 font-medium tracking-wider">
                <a href="index.php" class="hover:text-blue-600">HOME</a>
                <span class="mx-1">»</span>
                <a href="usulan.php" class="hover:text-blue-600">BERKAS USULAN</a>
                <span class="mx-1">»</span> DETAIL
            </div>

            <!-- Header Paket -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg font-bold text-slate-800">Detail Berkas Usulan</h1>
                        <?php if(isset($success_kirim)): ?>
                        <p class="text-emerald-600 text-sm font-medium mt-1">
                            <i class="fas fa-check-circle mr-1"></i><?= $success_kirim ?>
                        </p>
                        <?php else: ?>
                        <p class="text-slate-400 text-xs mt-1">
                            <i class="fas fa-clock mr-1"></i>Terakhir diperbarui: <?= date('d M Y H:i', strtotime($paket['updated_at'] ?? $paket['created_at'])) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <?php if(!$is_gagal && ($_SESSION['hak_akses'] ?? '') !== 'admin'): ?>
                        <button onclick="openModalUpdateRup()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-sync-alt text-xs"></i>
                            <span class="hidden sm:inline">Update Kode RUP</span>
                        </button>
                        <?php endif; ?>
                        <a href="usulan.php" class="flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-xl text-sm hover:bg-slate-50 transition text-slate-600">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span class="hidden sm:inline">Kembali</span>
                        </a>
                    </div>
                </div>
            </div>

            <?php 
            if(($_SESSION['hak_akses'] ?? '') === 'admin' && !$is_gagal): 
                $status_flow = ['dikirim', 'kaji_ulang', 'distribusi', 'penugasan', 'pemilihan_selesai'];
                $current_idx = array_search($paket['status'], $status_flow);
                $can_lanjut = $current_idx !== false && $current_idx < count($status_flow) - 1;
                $can_kembali = $current_idx !== false && $current_idx > 0;
                $next_status = $can_lanjut ? $status_flow[$current_idx + 1] : '';
                $prev_status = $can_kembali ? $status_flow[$current_idx - 1] : '';
            ?>
            <!-- Admin Panel Update Status -->
            <div class="bg-purple-50 border border-purple-200 p-5 rounded-2xl mb-5 shadow-sm">
                <h3 class="font-bold text-purple-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-shield-alt"></i> Admin Panel: Update Status Paket
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_status_admin">
                    <input type="text" name="catatan_admin" placeholder="Catatan opsional..." class="w-full px-4 py-2.5 rounded-xl border border-purple-200 text-sm focus:outline-none focus:border-purple-500 bg-white mb-3">
                    
                    <div class="flex flex-wrap gap-2 items-center">
                        <?php if($can_kembali): ?>
                            <button type="submit" name="new_status" value="<?= $prev_status ?>" class="px-4 py-2 bg-slate-500 hover:bg-slate-600 text-white rounded-xl text-sm font-semibold transition">
                                <i class="fas fa-arrow-left"></i> Kembali (<?= strtoupper(str_replace('_', ' ', $prev_status)) ?>)
                            </button>
                        <?php endif; ?>

                        <button type="submit" name="new_status" value="koreksi" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold transition">
                            <i class="fas fa-undo"></i> Kembalikan (Koreksi)
                        </button>

                        <button type="submit" name="new_status" value="gagal_pemilihan" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition" onclick="return confirm('Yakin ingin menandai sebagai Gagal Pemilihan? Paket tidak akan bisa diedit lagi.')">
                            <i class="fas fa-times-circle"></i> Gagal Pemilihan
                        </button>

                        <?php if($can_lanjut): ?>
                            <button type="submit" name="new_status" value="<?= $next_status ?>" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition ml-auto">
                                Lanjut (<?= strtoupper(str_replace('_', ' ', $next_status)) ?>) <i class="fas fa-arrow-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Posisi Berkas / Status Stepper -->
            <div class="bg-white border border-slate-100 rounded-2xl p-5 mb-5 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <!-- Paket Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest mb-1">POSISI BERKAS · PPK OPD</p>
                        <p class="text-xs text-slate-500 font-medium"><?= htmlspecialchars(strtoupper($paket['opd'])) ?></p>
                        <p class="font-bold text-slate-800 mt-0.5 leading-tight truncate"><?= htmlspecialchars($paket['nama_paket']) ?></p>
                        <p class="text-xs text-slate-400 mt-1">Kode RUP: <span class="font-mono text-blue-600"><?= htmlspecialchars($paket['kode_rup']) ?></span></p>
                    </div>
                    <!-- Status Badge -->
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center gap-2 px-4 py-2 <?= $st['class'] ?> rounded-xl text-sm font-bold">
                            <i class="fas fa-circle text-[8px]"></i>
                            <?= $st['label'] ?>
                        </span>
                    </div>
                </div>

                <!-- Status Stepper -->
                <?php
                $steps = [
                    ['key'=>'draft',            'label'=>'Draft',        'icon'=>'fa-file-alt'],
                    ['key'=>'dikirim',          'label'=>'Dikirim',      'icon'=>'fa-paper-plane'],
                    ['key'=>'kaji_ulang',       'label'=>'Kaji Ulang',   'icon'=>'fa-search'],
                    ['key'=>'distribusi',       'label'=>'Distribusi',   'icon'=>'fa-share-alt'],
                    ['key'=>'penugasan',        'label'=>'Penugasan',    'icon'=>'fa-user-check'],
                    ['key'=>'pemilihan_selesai','label'=>'Selesai',      'icon'=>'fa-check-circle'],
                ];
                $step_order = array_column($steps, 'key');
                $cur_idx = array_search($paket['status'], $step_order);
                if($paket['status'] == 'koreksi') $cur_idx = 1; // show at dikirim level
                if($paket['status'] == 'gagal_pemilihan') $cur_idx = 4;
                ?>
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-0 overflow-x-auto hide-scrollbar pb-1">
                        <?php foreach($steps as $si => $step):
                            $done   = ($cur_idx !== false && $si < $cur_idx);
                            $active = ($cur_idx !== false && $si == $cur_idx);
                        ?>
                        <div class="flex items-center <?= $si < count($steps)-1 ? 'flex-1' : '' ?> min-w-0">
                            <div class="flex flex-col items-center flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                            <?= $done ? 'step-done' : ($active ? 'step-active' : 'step-todo') ?>">
                                    <?php if($done): ?>
                                    <i class="fas fa-check text-[10px]"></i>
                                    <?php else: ?>
                                    <i class="fas <?= $step['icon'] ?> text-[10px]"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[9px] mt-1 text-center whitespace-nowrap px-1
                                             <?= $done ? 'text-emerald-600 font-semibold' : ($active ? 'text-blue-600 font-bold' : 'text-slate-400') ?>">
                                    <?= $step['label'] ?>
                                </span>
                            </div>
                            <?php if($si < count($steps)-1): ?>
                            <div class="flex-1 h-0.5 mx-1 mb-4 <?= $done ? 'bg-emerald-400' : 'bg-slate-200' ?>"></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <!-- Tab Navigation -->
                <div class="border-b border-slate-100 px-2 overflow-x-auto hide-scrollbar">
                    <div class="flex min-w-max">
                        <?php
                        // Hanya tampilkan tab yang aktif/berguna (sembunyikan placeholder)
                        $tabs = [
                            'detail'   => ['label'=>'Detail',          'icon'=>'fa-info-circle',   'badge'=>false],
                            'lampiran' => ['label'=>'Lampiran',        'icon'=>'fa-paperclip',     'badge'=>true, 'badge_color'=>'bg-red-500'],
                            'approval' => ['label'=>'Tindak Lanjut',   'icon'=>'fa-paper-plane',   'badge'=>true, 'badge_color'=>'bg-red-500'],
                            'ppk'      => ['label'=>'PPK',             'icon'=>'fa-user-tie',      'badge'=>false],
                            'transfer' => ['label'=>'Transfer',        'icon'=>'fa-exchange-alt',  'badge'=>false],
                            'log'      => ['label'=>'Log Berkas',      'icon'=>'fa-history',       'badge'=>false],
                        ];
                        // Tab coming soon (disembunyikan tapi masih bisa diakses via URL)
                        $coming_soon = ['tim','berita','chatting'];
                        foreach($tabs as $key => $tab_info):
                            $is_active = $active_tab == $key;
                        ?>
                        <a href="?id=<?= $id ?>&tab=<?= $key ?>"
                           class="flex items-center gap-2 px-4 py-3.5 text-xs font-semibold whitespace-nowrap border-b-2 transition
                                  <?= $is_active
                                      ? 'border-blue-600 text-blue-600'
                                      : 'border-transparent text-slate-500 hover:text-slate-700' ?>">
                            <i class="fas <?= $tab_info['icon'] ?> text-[11px]"></i>
                            <?= $tab_info['label'] ?>
                            <?php if(!empty($tab_info['badge'])): ?>
                            <span class="w-1.5 h-1.5 rounded-full <?= $tab_info['badge_color'] ?> flex-shrink-0"></span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="p-8">

                    <?php if($active_tab == 'detail'): ?>
                    <!-- DETAIL TAB -->
                    <p class="text-xs text-slate-400 mb-1">*Label warna <span class="text-red-500 font-bold">(merah)</span> diisi oleh sistem dari SIRUP</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Jenis Paket</label>
                            <p class="text-slate-800">Paket SPSE</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Kode SIRUP</label>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-800" id="display_kode_rup"><?= htmlspecialchars($paket['kode_rup']) ?></span>
                                <?php if(!$is_gagal && ($_SESSION['hak_akses'] ?? '') !== 'admin'): ?>
                                <button onclick="openModalUpdateRup()" class="text-blue-500 hover:text-blue-700 transition" title="Edit Kode SIRUP">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <a href="https://sirup.lkpp.go.id" target="_blank"
                                   class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs transition ml-2">
                                    <i class="fas fa-external-link-alt mr-1"></i> Buka SIRUP
                                </a>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Draft paket dapat di tarik ke APELBAJA satu hari setelah tanggal input di SPSE</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">URL Draft Paket</label>
                            <a href="<?= htmlspecialchars($paket['url_draft_spse']) ?>" target="_blank"
                               class="text-blue-600 hover:underline text-sm break-all">
                                <?= htmlspecialchars($paket['url_draft_spse']) ?>
                            </a>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Nama Pekerjaan</label>
                            <p class="text-slate-800 font-medium"><?= htmlspecialchars($paket['nama_paket']) ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tahun Anggaran</label>
                            <p class="text-slate-800"><?= htmlspecialchars($paket['tahun_anggaran']) ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Pagu</label>
                            <p class="text-slate-800 font-semibold">Rp <?= number_format($paket['pagu']??0,0,',','.') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">HPS</label>
                            <p class="font-semibold text-blue-700 text-lg">Rp <?= number_format($paket['hps']??0,0,',','.') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Sumber Dana</label>
                            <p class="text-slate-800"><?= htmlspecialchars($paket['sumber_dana']??'APBD') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Jenis Pengadaan</label>
                            <p class="text-slate-800"><?= htmlspecialchars($paket['jenis_pengadaan']??'-') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Jenis Kontrak</label>
                            <p class="text-slate-800"><?= htmlspecialchars($paket['jenis_kontrak']??'-') ?></p>
                        </div>
                        <?php if(!empty($paket['keterangan'])): ?>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Keterangan</label>
                            <p class="text-slate-800"><?= nl2br(htmlspecialchars($paket['keterangan'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tabel Anggaran -->
                    <div class="mt-8">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Anggaran</label>
                        <table class="w-full border border-slate-200 rounded-2xl overflow-hidden text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-slate-600 font-semibold">No.</th>
                                    <th class="text-left px-4 py-3 text-slate-600 font-semibold">Sumber Dana</th>
                                    <th class="text-left px-4 py-3 text-slate-600 font-semibold">Tahun Anggaran</th>
                                    <th class="text-left px-4 py-3 text-slate-600 font-semibold">MAK</th>
                                    <th class="text-left px-4 py-3 text-slate-600 font-semibold">Pagu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t border-slate-100">
                                    <td class="px-4 py-3">1</td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($paket['sumber_dana']??'APBD') ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($paket['tahun_anggaran']) ?></td>
                                    <td class="px-4 py-3 text-slate-500">-</td>
                                    <td class="px-4 py-3 font-semibold">Rp <?= number_format($paket['pagu']??0,0,',','.') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php elseif($active_tab == 'lampiran'): ?>
                    <!-- LAMPIRAN TAB -->
                    <div class="mb-4 text-sm text-slate-500">
                        *Maksimum ukuran setiap upload masing-masing <strong class="text-slate-700">2 MB</strong>
                    </div>

                    <?php if(isset($success_lamp)): ?>
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl mb-5 text-sm">
                        <i class="fas fa-check-circle mr-2"></i><?= $success_lamp ?>
                    </div>
                    <?php endif; ?>

                    <?php if(!$is_gagal && ($_SESSION['hak_akses'] ?? '') !== 'admin'): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_lampiran">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-slate-600">No.</th>
                                    <th class="text-left px-4 py-3 text-slate-600">Keterangan</th>
                                    <th class="text-left px-4 py-3 text-slate-600">File</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            <?php foreach($jenis_dokumen as $idx => $jdok):
                                // Cari lampiran yang sudah ada untuk tipe ini
                                $existing = null;
                                $lamps->data_seek(0);
                                while($l = $lamps->fetch_assoc()){
                                    if($l['tipe_dokumen'] == $jdok){ $existing = $l; break; }
                                }
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4"><?= $idx+1 ?></td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-slate-700"><?= htmlspecialchars($jdok) ?></p>
                                    <?php if($existing): ?>
                                    <a href="<?= htmlspecialchars($existing['file_path']) ?>" target="_blank"
                                       class="text-blue-600 text-xs mt-1 hover:underline">
                                        <i class="fas fa-file mr-1"></i><?= htmlspecialchars($existing['nama_file']) ?>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-2">
                                        <?php if(!$existing || $existing['status_validasi'] == 'revisi'): ?>
                                            <div>
                                                <input type="file" name="lampiran[]"
                                                       class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 file:text-xs hover:file:bg-slate-200">
                                                <input type="hidden" name="tipe_dokumen[]" value="<?= htmlspecialchars($jdok) ?>">
                                            </div>
                                        <?php endif; ?>

                                        <?php if($existing): ?>
                                            <?php if($existing['status_validasi'] == 'menunggu'): ?>
                                                <span class="inline-flex items-center w-max px-2 py-1 bg-amber-50 text-amber-600 rounded text-xs font-semibold"><i class="fas fa-clock mr-1"></i> Menunggu Verifikasi</span>
                                            <?php elseif($existing['status_validasi'] == 'valid'): ?>
                                                <span class="inline-flex items-center w-max px-2 py-1 bg-emerald-50 text-emerald-600 rounded text-xs font-semibold"><i class="fas fa-check-circle mr-1"></i> Sudah Benar</span>
                                            <?php elseif($existing['status_validasi'] == 'revisi'): ?>
                                                <div class="bg-red-50 text-red-600 border border-red-200 rounded-lg px-3 py-2 text-xs">
                                                    <p class="font-semibold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Perlu Diperbaiki</p>
                                                    <p><?= htmlspecialchars($existing['catatan_revisi']) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="flex justify-end mt-6">
                            <button type="submit"
                                    class="flex items-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-medium transition">
                                <i class="fas fa-upload"></i> Unggah
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-slate-600">No.</th>
                                    <th class="text-left px-4 py-3 text-slate-600">Keterangan</th>
                                    <th class="text-left px-4 py-3 text-slate-600">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            <?php foreach($jenis_dokumen as $idx => $jdok):
                                $existing = null;
                                $lamps->data_seek(0);
                                while($l = $lamps->fetch_assoc()){
                                    if($l['tipe_dokumen'] == $jdok){ $existing = $l; break; }
                                }
                            ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4"><?= $idx+1 ?></td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-slate-700"><?= htmlspecialchars($jdok) ?></p>
                                    <?php if($existing): ?>
                                    <a href="<?= htmlspecialchars($existing['file_path']) ?>" target="_blank"
                                       class="text-blue-600 text-xs mt-1 hover:underline">
                                        <i class="fas fa-file mr-1"></i><?= htmlspecialchars($existing['nama_file']) ?>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <?php if($existing): ?>
                                        <?php if(($_SESSION['hak_akses'] ?? '') === 'admin' && !$is_gagal): ?>
                                            <!-- Validasi Form for Admin -->
                                            <form method="POST" class="bg-slate-100 p-3 rounded-xl border border-slate-200">
                                                <input type="hidden" name="action" value="validasi_lampiran">
                                                <input type="hidden" name="lampiran_id" value="<?= $existing['id'] ?>">
                                                
                                                <div class="flex items-center gap-3 mb-2">
                                                    <label class="flex items-center gap-1 cursor-pointer">
                                                        <input type="radio" name="status_validasi" value="valid" <?= $existing['status_validasi'] == 'valid' ? 'checked' : '' ?> onchange="document.getElementById('catatan_div_<?= $existing['id'] ?>').style.display='none'" required>
                                                        <span class="text-emerald-600 font-medium text-xs"><i class="fas fa-check"></i> Diterima</span>
                                                    </label>
                                                    <label class="flex items-center gap-1 cursor-pointer">
                                                        <input type="radio" name="status_validasi" value="revisi" <?= $existing['status_validasi'] == 'revisi' ? 'checked' : '' ?> onchange="document.getElementById('catatan_div_<?= $existing['id'] ?>').style.display='block'" required>
                                                        <span class="text-red-600 font-medium text-xs"><i class="fas fa-times"></i> Revisi</span>
                                                    </label>
                                                    <?php if($existing['status_validasi'] == 'menunggu'): ?>
                                                        <span class="ml-auto text-amber-500 text-[10px] uppercase font-bold tracking-wider">Menunggu Check</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div id="catatan_div_<?= $existing['id'] ?>" style="display: <?= $existing['status_validasi'] == 'revisi' ? 'block' : 'none' ?>;">
                                                    <textarea name="catatan_revisi" rows="2" placeholder="Tulis catatan revisi..." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:border-blue-500 mb-2"><?= htmlspecialchars($existing['catatan_revisi'] ?? '') ?></textarea>
                                                </div>
                                                
                                                <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition">
                                                    Simpan Validasi
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Readonly status -->
                                            <?php if($existing['status_validasi'] == 'menunggu'): ?>
                                                <span class="text-amber-500 text-xs font-semibold"><i class="fas fa-clock mr-1"></i> Menunggu</span>
                                            <?php elseif($existing['status_validasi'] == 'valid'): ?>
                                                <span class="text-emerald-500 text-xs font-semibold"><i class="fas fa-check-circle mr-1"></i> Diterima</span>
                                            <?php elseif($existing['status_validasi'] == 'revisi'): ?>
                                                <span class="text-red-600 text-xs font-semibold"><i class="fas fa-times-circle mr-1"></i> Revisi</span>
                                                <p class="text-[10px] text-slate-500 mt-1"><?= htmlspecialchars($existing['catatan_revisi']) ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php elseif($active_tab == 'approval'): ?>
                    <!-- APPROVAL TAB -->
                    <div class="mb-4">
                        <h3 class="font-semibold text-slate-700 mb-1">TINDAK LANJUT – PPK OPD</h3>
                        <p class="text-xs text-slate-400">Pastikan semua berkas sudah lengkap sebelum mengirim ke UKPBJ.</p>
                    </div>

                    <?php 
                    $stat = $paket['status'];
                    if(!$is_gagal && in_array($stat, ['draft', 'koreksi']) && ($_SESSION['hak_akses'] ?? '') !== 'admin'): 
                    ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="kirim_ukpbj">

                        <div class="bg-slate-50 rounded-2xl p-6 mb-6">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="tindak_lanjut" value="setuju" required
                                       class="mt-1 w-4 h-4 text-blue-600 rounded">
                                <span class="text-sm text-slate-700">
                                    <strong>Setuju untuk dikirim ke UKPBJ Prov. Jatim</strong><br>
                                    <span class="text-slate-500">Bahwa kelengkapan berkas yang dikirim (diupload) adalah benar, telah kami periksa dan kami setujui selaku Pejabat Pembuat Komitmen (PPK), BAG. PENGELOLAAN.</span>
                                </span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-5 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1.5">LAMPIRAN <span class="text-slate-400 font-normal">(jika ada)</span></label>
                                <input type="file" name="lampiran_kirim"
                                       class="w-full text-sm text-slate-600 file:mr-2 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1.5">CATATAN</label>
                                <textarea name="catatan" rows="3"
                                          class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:border-blue-500 text-sm resize-none"
                                          placeholder="Catatan tambahan (opsional)..."></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="flex items-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-semibold transition active:scale-95 shadow-sm">
                                <i class="fas fa-paper-plane"></i> Kirim
                            </button>
                        </div>
                    </form>
                    <?php else: 
                        $state_ui = [
                            'dikirim'           => ['icon'=>'fa-paper-plane',  'color'=>'blue',    'title'=>'Paket Sudah Dikirim', 'desc'=>'Menunggu proses kaji ulang dari Bagian Pengelolaan PBJ.'],
                            'kaji_ulang'        => ['icon'=>'fa-search',       'color'=>'amber',   'title'=>'Proses Kaji Ulang', 'desc'=>'Paket sedang dalam tahap pengecekan dan verifikasi oleh tim UKPBJ.'],
                            'distribusi'        => ['icon'=>'fa-share-alt',    'color'=>'purple',  'title'=>'Distribusi Berkas', 'desc'=>'Berkas sedang didistribusikan ke Pokja Pemilihan yang bertugas.'],
                            'penugasan'         => ['icon'=>'fa-user-check',   'color'=>'indigo',  'title'=>'Penugasan Pokja', 'desc'=>'Pokja Pemilihan telah ditugaskan untuk memproses paket ini.'],
                            'pemilihan_selesai' => ['icon'=>'fa-check-circle', 'color'=>'emerald', 'title'=>'Pemilihan Selesai', 'desc'=>'Proses pemilihan untuk paket ini telah dinyatakan selesai.'],
                            'gagal_pemilihan'   => ['icon'=>'fa-times-circle', 'color'=>'red',     'title'=>'Gagal Pemilihan', 'desc'=>'Proses pemilihan untuk paket ini dinyatakan gagal atau dibatalkan.']
                        ];
                        $ui = $state_ui[$stat] ?? ['icon'=>'fa-info-circle', 'color'=>'slate', 'title'=>'Status: '.strtoupper(str_replace('_',' ',$stat)), 'desc'=>'Paket sedang diproses dalam sistem.'];
                    ?>
                    <div class="bg-<?= $ui['color'] ?>-50 border border-<?= $ui['color'] ?>-200 text-<?= $ui['color'] ?>-700 p-5 rounded-2xl flex items-center gap-4 mb-6 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-<?= $ui['color'] ?>-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas <?= $ui['icon'] ?> text-2xl text-<?= $ui['color'] ?>-600"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg"><?= $ui['title'] ?></p>
                            <p class="text-sm mt-0.5 opacity-90"><?= $ui['desc'] ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php elseif($active_tab == 'ppk'): ?>
                    <!-- PPK TAB -->
                    <div class="bg-slate-50 rounded-2xl p-6">
                        <h3 class="font-semibold text-slate-700 mb-4">Informasi PPK</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="text-xs text-slate-500 uppercase tracking-wide">Nama PPK</label>
                                <p class="font-medium text-slate-800 mt-1"><?= htmlspecialchars($paket['nama_user']) ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 uppercase tracking-wide">OPD</label>
                                <p class="font-medium text-slate-800 mt-1"><?= htmlspecialchars($paket['opd']) ?></p>
                            </div>
                        </div>
                    </div>

                    <?php elseif($active_tab == 'tim'): ?>
                    <!-- TIM PERENCANAAN TAB -->
                    <div class="text-center py-12 text-slate-400">
                        <i class="fas fa-users text-4xl mb-3"></i>
                        <p>Belum ada data tim perencanaan.</p>
                    </div>

                    <?php elseif($active_tab == 'berita'): ?>
                    <!-- BERITA ACARA TAB -->
                    <div class="text-center py-12 text-slate-400">
                        <i class="fas fa-file-alt text-4xl mb-3"></i>
                        <p>Belum ada berita acara / catatan.</p>
                    </div>

                    <?php elseif($active_tab == 'chatting'): ?>
                    <!-- CHATTING TAB -->
                    <div class="text-center py-12 text-slate-400">
                        <i class="fas fa-comments text-4xl mb-3"></i>
                        <p>Fitur chatting belum tersedia.</p>
                    </div>

                    <?php elseif($active_tab == 'log'): ?>
                    <!-- LOG BERKAS TAB -->
                    <div class="flex justify-end mb-4">
                        <button onclick="window.print()"
                                class="flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-xl text-sm hover:bg-slate-50 transition text-slate-600">
                            <i class="fas fa-print"></i> Cetak Log
                        </button>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-left px-4 py-3 text-slate-600">TANGGAL</th>
                                <th class="text-left px-4 py-3 text-slate-600">NAMA PENGGUNA</th>
                                <th class="text-left px-4 py-3 text-slate-600">STATUS</th>
                                <th class="text-left px-4 py-3 text-slate-600">KETERANGAN</th>
                                <th class="text-left px-4 py-3 text-slate-600">LAMPIRAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <?php if($logs && $logs->num_rows > 0): ?>
                            <?php while($log = $logs->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                    <?= date('d M Y H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($log['nama_pengguna'] ?? $log['nm'] ?? '-') ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs">
                                        <?= htmlspecialchars($log['aksi']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($log['keterangan'] ?? '-') ?></td>
                                <td class="px-4 py-3">
                                    <?php if(!empty($log['lampiran_file'])): ?>
                                    <a href="<?= htmlspecialchars($log['lampiran_file']) ?>" target="_blank"
                                       class="text-blue-600 hover:underline text-xs"><i class="fas fa-paperclip mr-1"></i>Lampiran</a>
                                    <?php else: ?>
                                    <span class="text-slate-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <?php elseif($active_tab == 'transfer'): ?>
                    <!-- TRANSFER TAB -->
                    <div class="mb-4 flex justify-between items-end">
                        <div>
                            <h3 class="font-semibold text-slate-700 mb-1">RIWAYAT TRANSFER</h3>
                            <p class="text-xs text-slate-400">Jejak pengalihan hak akses dan tanggung jawab untuk paket ini.</p>
                        </div>
                        <?php if(!$is_gagal && ($_SESSION['hak_akses'] ?? '') !== 'admin' && ($_SESSION['user_id'] == $paket['pp_id'] || $_SESSION['user_id'] == $paket['ppk_id'])): ?>
                        <a href="index.php?page=transfer_ajukan" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2">
                            <i class="fas fa-exchange-alt"></i> Ajukan Transfer
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php 
                    require_once BASEPATH . '/app/models/AssignmentTransfer.php';
                    $atModel = new AssignmentTransfer($conn);
                    $historiTransfer = $atModel->getHistoryByPaket($id);
                    ?>

                    <table class="w-full text-left text-sm text-slate-600 mt-4">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Tanggal Pengajuan</th>
                                <th class="px-6 py-4">Dari User</th>
                                <th class="px-6 py-4">Ke User</th>
                                <th class="px-6 py-4">Tipe</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($historiTransfer)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada riwayat transfer paket.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($historiTransfer as $t): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-slate-800"><?= date('d/m/Y', strtotime($t['created_at'])) ?></div>
                                            <div class="text-xs text-slate-400"><?= date('H:i', strtotime($t['created_at'])) ?> WIB</div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-700 font-medium"><?= htmlspecialchars($t['nama_dari']) ?></td>
                                        <td class="px-6 py-4 text-slate-700 font-medium"><?= htmlspecialchars($t['nama_ke']) ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold rounded-md bg-indigo-50 text-indigo-600 border border-indigo-200 uppercase">
                                                <?= htmlspecialchars($t['tipe_transfer']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($t['status'] === 'menunggu'): ?>
                                                <span class="text-amber-600 text-xs font-semibold"><i class="fas fa-clock mr-1"></i> Menunggu</span>
                                            <?php elseif ($t['status'] === 'disetujui'): ?>
                                                <span class="text-emerald-600 text-xs font-semibold"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                                                <p class="text-[10px] text-slate-400 mt-1">Oleh: <?= htmlspecialchars($t['nama_admin']) ?></p>
                                            <?php elseif ($t['status'] === 'ditolak'): ?>
                                                <span class="text-rose-600 text-xs font-semibold"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                                                <p class="text-[10px] text-slate-500 mt-1">Oleh: <?= htmlspecialchars($t['nama_admin']) ?> (<?= htmlspecialchars($t['catatan_admin']) ?>)</p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Modal Update RUP -->
        <div id="modalUpdateRup" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md flex flex-col transform scale-95 transition-transform duration-300" id="modalUpdateRupContent">
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800">Ubah Kode SIRUP</h2>
                    <button type="button" onclick="closeModalUpdateRup()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" class="p-6">
                    <input type="hidden" name="action" value="update_rup">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Kode SIRUP Baru</label>
                    <input type="text" name="kode_rup" value="<?= htmlspecialchars($paket['kode_rup']) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition mb-6" required>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModalUpdateRup()" class="px-6 py-2.5 border border-slate-300 text-slate-600 rounded-xl hover:bg-slate-100 transition text-sm font-medium">Batal</button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition active:scale-95 shadow-sm text-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function openModalUpdateRup() {
            const modal = document.getElementById('modalUpdateRup');
            const content = document.getElementById('modalUpdateRupContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);
        }
        function closeModalUpdateRup() {
            const modal = document.getElementById('modalUpdateRup');
            const content = document.getElementById('modalUpdateRupContent');
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        </script>

        <?php include 'includes/footer.php'; ?>
    </div>
</div>



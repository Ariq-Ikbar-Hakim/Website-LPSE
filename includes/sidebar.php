<?php
// includes/sidebar.php
?>

<!-- ─── Sidebar Overlay (mobile) ─── -->
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"
     onclick="closeSidebar()"></div>

<!-- ─── Sidebar ─── -->
<aside id="sidebar"
       class="w-72 bg-slate-900 text-white flex flex-col min-h-screen
              fixed left-0 top-0 z-40
              -translate-x-full lg:translate-x-0">

    <!-- Logo Header -->
    <div class="p-5 border-b border-slate-700/60">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg flex-shrink-0">
                    <img src="assets/logo.png" alt="APELBAJA" class="w-8 h-8 object-contain">
                </div>
                <div>
                    <h1 class="font-bold text-base tracking-tight leading-tight">APELBAJA</h1>
                    <p class="text-[11px] text-slate-400 leading-tight">Kab. Bangkalan</p>
                </div>
            </div>
            <!-- Close button (mobile only) -->
            <button onclick="closeSidebar()"
                    class="lg:hidden w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 text-slate-400 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-0.5 overflow-y-auto">

        <!-- ─── MENU UTAMA ─── -->
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest px-3 pt-3 pb-1.5">Menu Utama</p>

        <a href="index.php"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  <?= basename($_SERVER['PHP_SELF'])=='index.php'
                      ? 'bg-white text-slate-900 shadow-md font-semibold'
                      : 'hover:bg-white/10 text-slate-300' ?>">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                         <?= basename($_SERVER['PHP_SELF'])=='index.php' ? 'bg-blue-100 text-blue-600' : 'bg-white/5 text-slate-400' ?>">
                <i class="fas fa-home text-sm"></i>
            </span>
            <span class="text-sm">Dashboard</span>
        </a>

        <?php if(($_SESSION['jabatan_aktif'] ?? '') === 'admin'): ?>
        <!-- ─── ADMIN PANEL ─── -->
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest px-3 pt-4 pb-1.5">Admin Panel</p>

        <a href="admin_users.php"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  <?= basename($_SERVER['PHP_SELF'])=='admin_users.php'
                      ? 'bg-white text-slate-900 shadow-md font-semibold'
                      : 'hover:bg-white/10 text-slate-300' ?>">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                         <?= basename($_SERVER['PHP_SELF'])=='admin_users.php' ? 'bg-purple-100 text-purple-600' : 'bg-white/5 text-slate-400' ?>">
                <i class="fas fa-users-cog text-sm"></i>
            </span>
            <span class="text-sm flex-1">Konfirmasi Akun</span>
            <?php
            $cr2 = $conn->query("SELECT COUNT(*) AS c FROM users WHERE status_aktif=0");
            $cn2 = $cr2 ? $cr2->fetch_assoc()['c'] : 0;
            if($cn2 > 0): ?>
            <span class="badge-urgent ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold"><?= $cn2 ?></span>
            <?php endif; ?>
        </a>

        <a href="index.php?page=admin_transfer_paket"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  <?= ($_GET['page'] ?? '')=='admin_transfer_paket'
                      ? 'bg-white text-slate-900 shadow-md font-semibold'
                      : 'hover:bg-white/10 text-slate-300' ?>">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                         <?= ($_GET['page'] ?? '')=='admin_transfer_paket' ? 'bg-indigo-100 text-indigo-600' : 'bg-white/5 text-slate-400' ?>">
                <i class="fas fa-exchange-alt text-sm"></i>
            </span>
            <span class="text-sm flex-1">Transfer Jabatan & Paket</span>
            <?php
            $cr_tr = $conn->query("SELECT COUNT(*) AS c FROM assignment_transfer WHERE status='menunggu'");
            $cn_tr = $cr_tr ? $cr_tr->fetch_assoc()['c'] : 0;
            if($cn_tr > 0): ?>
            <span class="badge-urgent ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold"><?= $cn_tr ?></span>
            <?php endif; ?>
        </a>

        <?php endif; ?>

        <!-- ─── ADMINISTRASI ─── -->
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest px-3 pt-4 pb-1.5">Administrasi</p>

        <a href="managemen_user.php"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  <?= basename($_SERVER['PHP_SELF'])=='managemen_user.php'
                      ? 'bg-white text-slate-900 shadow-md font-semibold'
                      : 'hover:bg-white/10 text-slate-300' ?>">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                         <?= basename($_SERVER['PHP_SELF'])=='managemen_user.php' ? 'bg-emerald-100 text-emerald-600' : 'bg-white/5 text-slate-400' ?>">
                <i class="fas fa-id-card text-sm"></i>
            </span>
            <span class="text-sm">SK OPD</span>
        </a>

        <!-- ─── USULAN PAKET ─── -->
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest px-3 pt-4 pb-1.5">Usulan Paket</p>

        <a href="usulan.php"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  <?= basename($_SERVER['PHP_SELF'])=='usulan.php'
                      ? 'bg-white text-slate-900 shadow-md font-semibold'
                      : 'hover:bg-white/10 text-slate-300' ?>">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                         <?= basename($_SERVER['PHP_SELF'])=='usulan.php' ? 'bg-blue-100 text-blue-600' : 'bg-white/5 text-slate-400' ?>">
                <i class="fas fa-file-invoice text-sm"></i>
            </span>
            <span class="text-sm">Berkas Usulan</span>
        </a>

        <?php if(($_SESSION['jabatan_aktif'] ?? '') !== 'admin'): ?>
        <!-- ─── TRANSFER JABATAN & PAKET ─── -->
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest px-3 pt-4 pb-1.5">Transfer Paket</p>

        <a href="index.php?page=transfer_saya"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  <?= ($_GET['page'] ?? '')=='transfer_saya' || ($_GET['page'] ?? '')=='transfer_ajukan'
                      ? 'bg-white text-slate-900 shadow-md font-semibold'
                      : 'hover:bg-white/10 text-slate-300' ?>">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                         <?= ($_GET['page'] ?? '')=='transfer_saya' || ($_GET['page'] ?? '')=='transfer_ajukan' ? 'bg-indigo-100 text-indigo-600' : 'bg-white/5 text-slate-400' ?>">
                <i class="fas fa-exchange-alt text-sm"></i>
            </span>
            <span class="text-sm">Transfer Jabatan & Paket</span>
        </a>
        <?php endif; ?>

        <!-- ─── PENGEMBALIAN ─── -->
        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest px-3 pt-4 pb-1.5">Pengembalian</p>

        <a href="pengembalian_paket.php"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  <?= basename($_SERVER['PHP_SELF'])=='pengembalian_paket.php'
                      ? 'bg-white text-slate-900 shadow-md font-semibold'
                      : 'hover:bg-white/10 text-slate-300' ?>">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                         <?= basename($_SERVER['PHP_SELF'])=='pengembalian_paket.php' ? 'bg-amber-100 text-amber-600' : 'bg-white/5 text-slate-400' ?>">
                <i class="fas fa-arrow-rotate-left text-sm"></i>
            </span>
            <span class="text-sm flex-1">Pengembalian Paket</span>
            <?php
            if(isset($_SESSION['user_id'])){
                global $conn;
                $is_admin_sidebar = ($_SESSION['jabatan_aktif'] ?? '') === 'admin';
                $uid_s = (int)$_SESSION['user_id'];
                if ($is_admin_sidebar) {
                    $cr = $conn->query("SELECT COUNT(*) AS c FROM paket WHERE status='koreksi'");
                } else {
                    $cr = $conn->query("SELECT COUNT(*) AS c FROM paket WHERE user_id='$uid_s' AND status='koreksi'");
                }
                $cn = $cr ? $cr->fetch_assoc()['c'] : 0;
                if($cn > 0): ?>
            <span class="badge-urgent ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold"><?= $cn ?></span>
            <?php endif; } ?>
        </a>

    </nav>

    <!-- User Profile & Logout -->
    <div class="border-t border-slate-700/60 p-4 space-y-1">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition cursor-pointer">
            <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white truncate leading-tight"><?= htmlspecialchars($_SESSION['nama'] ?? 'PPK') ?></p>
                <p class="text-[11px] text-slate-400 leading-tight"><?= htmlspecialchars(strtoupper($_SESSION['jabatan_aktif'] ?? 'PPK')) ?></p>
            </div>
        </div>

        <a href="logout.php"
           class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-red-500/20 text-red-400 transition-all">
            <span class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-sign-out-alt text-sm"></i>
            </span>
            <span class="text-sm">Keluar</span>
        </a>
    </div>
</aside>

<!-- ─── Sidebar JS ─── -->
<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.add('hidden');
        document.body.style.overflow = '';
    }
    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
</script>

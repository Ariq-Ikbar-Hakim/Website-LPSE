<nav class="bg-white border-b border-slate-200 px-4 lg:px-8 py-3 flex items-center justify-between shadow-sm sticky top-0 z-20">

    <div class="flex items-center gap-3">
        <!-- Hamburger (mobile only) -->
        <button onclick="openSidebar()" id="hamburger-btn"
                class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-600 transition"
                aria-label="Buka Menu">
            <i class="fas fa-bars text-base"></i>
        </button>

        <!-- Breadcrumb -->
        <div class="text-sm text-slate-500">
            <span class="font-semibold text-slate-700">UKPBJ</span>
            <span class="mx-2 text-slate-300 hidden sm:inline">|</span>
            <span class="hidden sm:inline">Provinsi Jawa Timur</span>
        </div>
    </div>

    <div class="flex items-center gap-2 lg:gap-4">
        <!-- Tahun label -->
        <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-xl font-semibold hidden sm:inline-flex items-center gap-1">
            <i class="fas fa-calendar-alt text-[10px]"></i>
            TA <strong><?= date('Y') ?></strong>
        </span>

        <!-- User info (desktop) -->
        <div class="hidden md:flex items-center gap-3">
            <div class="text-right">
                <p class="text-[11px] text-slate-400 leading-none"><?= htmlspecialchars(strtoupper($_SESSION['hak_akses'] ?? 'PPK')) ?></p>
                <p class="text-sm font-semibold text-slate-800 leading-tight mt-0.5">
                    <?= htmlspecialchars($_SESSION['nama'] ?? 'PPK') ?>
                </p>
            </div>
            <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center text-white text-sm font-bold shadow">
                <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?>
            </div>
        </div>

        <!-- Refresh -->
        <button onclick="window.location.reload()" title="Refresh"
                class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-500 transition"
                aria-label="Refresh halaman">
            <i class="fas fa-sync-alt text-sm"></i>
        </button>

        <!-- Logout -->
        <a href="logout.php"
           class="flex items-center gap-2 px-3 lg:px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-sm transition font-medium border border-red-100"
           aria-label="Logout">
            <i class="fas fa-sign-out-alt"></i>
            <span class="hidden md:inline">Keluar</span>
        </a>
    </div>
</nav>

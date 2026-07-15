<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $title ?? 'Dashboard' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar { transition: all 0.3s ease; }
        .content { transition: all 0.3s ease; }
    </style>
</head>
<body class="text-slate-800">

    <!-- Sidebar -->
    <aside class="sidebar w-64 bg-slate-900 h-screen fixed top-0 left-0 overflow-y-auto shadow-xl z-20 text-slate-300 flex flex-col">
        <div class="p-6 flex items-center gap-3 border-b border-slate-800">
            <div class="bg-blue-600 p-2 rounded-lg text-white">
                <i class="fas fa-file-contract text-xl"></i>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg leading-tight tracking-wide">APELBAJA</h1>
                <div class="text-xs text-slate-400">v<?= APP_VERSION ?></div>
            </div>
        </div>

        <div class="p-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold">
                    <?= substr($_SESSION['nama'] ?? 'U', 0, 1) ?>
                </div>
                <div class="overflow-hidden">
                    <div class="text-white font-medium text-sm truncate"><?= $_SESSION['nama'] ?? 'User' ?></div>
                    <div class="text-xs text-blue-400 font-semibold mt-0.5"><?= $_SESSION['jabatan_aktif'] ?? 'Role' ?></div>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1">
            <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= activeNav('dashboard') ?> transition-all duration-200">
                <i class="fas fa-home w-5 text-center <?= activeNavIcon('dashboard', 'text-blue-600', '') ?>"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengadaan</p>
            </div>

            <a href="index.php?page=paket_index" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= activeNav('paket_index') ?> transition-all duration-200">
                <i class="fas fa-box w-5 text-center <?= activeNavIcon('paket_index', 'text-emerald-500', '') ?>"></i>
                <span class="font-medium">Daftar Paket</span>
            </a>

            <a href="index.php?page=ba_index" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= activeNav('ba_index') ?> transition-all duration-200">
                <i class="fas fa-file-signature w-5 text-center <?= activeNavIcon('ba_index', 'text-amber-500', '') ?>"></i>
                <span class="font-medium">Berita Acara</span>
            </a>

            <?php if (isRole('admin')): ?>
                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Administrator</p>
                </div>
                
                <a href="index.php?page=admin_konfirmasi" class="flex items-center justify-between px-4 py-3 rounded-xl <?= activeNav('admin_konfirmasi') ?> transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-user-check w-5 text-center <?= activeNavIcon('admin_konfirmasi', 'text-purple-500', '') ?>"></i>
                        <span class="font-medium">Verifikasi Akun</span>
                    </div>
                </a>
                
                <a href="index.php?page=admin_reset_password" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= activeNav('admin_reset_password') ?> transition-all duration-200">
                    <i class="fas fa-key w-5 text-center <?= activeNavIcon('admin_reset_password', 'text-rose-500', '') ?>"></i>
                    <span class="font-medium">Reset Password</span>
                </a>

                <a href="index.php?page=admin_transfer_paket" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= activeNav('admin_transfer_paket') ?> transition-all duration-200">
                    <i class="fas fa-exchange-alt w-5 text-center <?= activeNavIcon('admin_transfer_paket', 'text-indigo-500', '') ?>"></i>
                    <span class="font-medium">Transfer Jabatan & Paket</span>
                </a>
            <?php endif; ?>

            <?php if (!isRole('admin')): ?>
                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Lainnya</p>
                </div>
                
                <a href="index.php?page=transfer_saya" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= activeNav('transfer_saya') ?> transition-all duration-200">
                    <i class="fas fa-exchange-alt w-5 text-center <?= activeNavIcon('transfer_saya', 'text-indigo-500', '') ?>"></i>
                    <span class="font-medium">Transfer Jabatan & Paket</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="index.php?page=logout" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="font-medium">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="content ml-64 min-h-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-8 sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-slate-800"><?= $title ?? 'Dashboard' ?></h2>
            </div>
            <div class="flex items-center gap-6">
                <!-- Tahun Anggaran selector (bisa global atau per page) -->
                <div class="text-sm text-slate-500">
                    <i class="far fa-calendar-alt mr-2"></i><?= date('l, d M Y') ?>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if (flashHas('success')): ?>
            <div class="flash-alert m-8 mb-0 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl shadow-sm flex items-start gap-3">
                <i class="fas fa-check-circle text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold">Berhasil!</h4>
                    <p class="text-sm mt-1"><?= flashGet('success') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (flashHas('error')): ?>
            <div class="flash-alert m-8 mb-0 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl shadow-sm flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold">Terjadi Kesalahan!</h4>
                    <p class="text-sm mt-1"><?= flashGet('error') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Content Area -->
        <div class="p-8 flex-1">
            <?= $content ?? '' ?>
        </div>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-8 text-sm text-slate-500 flex justify-between items-center">
            <div>&copy; <?= date('Y') ?> Biro Pengadaan Barang/Jasa Setda Prov. Jatim</div>
            <div class="font-medium text-slate-400">Versi <?= APP_VERSION ?></div>
        </footer>
    </main>

    <script>
        // Sembunyikan flash message otomatis setelah 5 detik
        setTimeout(() => {
            document.querySelectorAll('.flash-alert').forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.5s ease';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>

<?php ob_start(); ?>

<div class="w-full max-w-md">

    <!-- Logo -->
    <div class="flex justify-center items-center mb-8">
        <img src="assets/images/logo.png" alt="APEL BAJA Tender" class="h-20 w-auto drop-shadow-lg" onerror="this.style.display='none'">
        <div class="text-white text-3xl font-bold ml-3">APELBAJA</div>
    </div>

    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <h3 class="text-2xl font-semibold text-white">Lupa Password</h3>
            <p class="text-slate-400 mt-2 text-sm">Masukkan NIP Anda, admin akan memeriksa dan mengirimkan link reset ke email Anda.</p>
        </div>

        <?php if (flashHas('error')): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-2xl mb-6 text-center text-sm">
                <?= flashGet('error') ?>
            </div>
        <?php endif; ?>

        <?php if (flashHas('success')): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-2xl mb-6 text-center text-sm">
                <i class="fas fa-check-circle mb-2 text-2xl"></i><br>
                <?= flashGet('success') ?>
            </div>
            
            <div class="mt-6 space-y-3">
                <p class="text-slate-300 text-sm text-center">Anda dapat menghubungi Admin untuk mempercepat proses:</p>
                <a href="https://wa.me/<?= str_replace('08','628',ADMIN_WA_1) ?>" target="_blank" class="flex items-center justify-center gap-2 w-full bg-[#25D366] hover:bg-[#1ebd5a] text-white py-3 rounded-xl font-medium transition">
                    <i class="fab fa-whatsapp"></i> Hubungi Admin 1
                </a>
                <a href="https://wa.me/<?= str_replace('08','628',ADMIN_WA_2) ?>" target="_blank" class="flex items-center justify-center gap-2 w-full bg-[#25D366] hover:bg-[#1ebd5a] text-white py-3 rounded-xl font-medium transition">
                    <i class="fab fa-whatsapp"></i> Hubungi Admin 2
                </a>
            </div>
            
            <div class="text-center mt-6">
                <a href="index.php?page=login" class="text-slate-400 hover:text-white text-sm transition">
                    Kembali ke <span class="text-emerald-400 font-medium">Login</span>
                </a>
            </div>
            
        <?php else: ?>

            <form method="POST" action="index.php?page=lupa_password" class="space-y-5">
                <?= csrfField() ?>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">NIP</label>
                    <input type="text" name="nip" required autocomplete="off"
                           class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500 transition">
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-2xl font-semibold transition mt-2 shadow-lg">
                    KIRIM PERMINTAAN
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="index.php?page=login" class="text-slate-400 hover:text-white text-sm transition">
                    Kembali ke <span class="text-emerald-400 font-medium">Login</span>
                </a>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/auth.php';
?>

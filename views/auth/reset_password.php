<?php ob_start(); ?>

<div class="w-full max-w-md">

    <!-- Logo -->
    <div class="flex justify-center items-center mb-8">
        <img src="assets/images/logo.png" alt="APEL BAJA Tender" class="h-20 w-auto drop-shadow-lg" onerror="this.style.display='none'">
        <div class="text-white text-3xl font-bold ml-3">APELBAJA</div>
    </div>

    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8">

        <div class="text-center mb-8">
            <h3 class="text-2xl font-semibold text-white">Reset Password</h3>
            <p class="text-slate-400 mt-2 text-sm">Silakan masukkan password baru Anda.</p>
        </div>

        <?php if (flashHas('error')): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-2xl mb-6 text-center text-sm">
                <?= flashGet('error') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=update_password" class="space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Password Baru <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="6"
                           placeholder="Minimal 6 karakter"
                           class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500 transition"
                           style="padding-right: 48px;">
                    <button type="button" onclick="togglePassword('password', 'eye-reset')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition focus:outline-none" tabindex="-1">
                        <svg id="eye-reset" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Konfirmasi Password Baru <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="password" name="password_confirm" id="password_confirm" required minlength="6"
                           placeholder="Ulangi password baru"
                           class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500 transition"
                           style="padding-right: 48px;">
                    <button type="button" onclick="togglePassword('password_confirm', 'eye-confirm')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition focus:outline-none" tabindex="-1">
                        <svg id="eye-confirm" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-2xl font-semibold transition mt-2 shadow-lg">
                UBAH PASSWORD
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="index.php?page=login" class="text-slate-400 hover:text-white text-sm transition">
                Kembali ke <span class="text-emerald-400 font-medium">Login</span>
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.122-3.364M6.53 6.53A9.97 9.97 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.132 5.411M3 3l18 18"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
}
</script>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/auth.php';
?>

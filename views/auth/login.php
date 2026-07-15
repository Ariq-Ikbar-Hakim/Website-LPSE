<?php ob_start(); ?>

<!-- reCAPTCHA v3 (invisible, background scoring) -->
<script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_V3_SITE_KEY ?>"></script>
<!-- reCAPTCHA v2 (checkbox widget) -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
.field-error {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    font-size: 0.78rem;
    color: #f87171;
    animation: fadeIn .2s ease;
}
.field-error svg { flex-shrink: 0; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }

.char-counter { font-size: 0.72rem; color: #94a3b8; text-align: right; margin-top: 4px; }
.char-counter.ok  { color: #34d399; }
.char-counter.bad { color: #f87171; }
</style>

<div class="w-full max-w-md">

    <!-- Logo -->
    <div class="flex justify-center items-center mb-8">
        <img src="assets/images/logo.png"
             alt="APEL BAJA Tender"
             class="h-20 w-auto drop-shadow-lg" onerror="this.style.display='none'">
        <div class="text-white text-3xl font-bold ml-3">APELBAJA</div>
    </div>

    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8">

        <div class="text-center mb-8">
            <h3 class="text-2xl font-semibold text-white">Selamat Datang di LPSE</h3>
            <p class="text-slate-400 mt-2">Masuk ke sistem pengadaan</p>
        </div>

        <?php if (flashHas('error')): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-2xl mb-6 text-center text-sm">
                <?= flashGet('error') ?>
            </div>
        <?php endif; ?>

        <?php if (flashHas('success')): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-2xl mb-6 text-center text-sm">
                <?= flashGet('success') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=login" class="space-y-5" id="loginForm">
            <?= csrfField() ?>
            <!-- Token v3 (diisi JS sebelum submit) -->
            <input type="hidden" name="recaptcha_v3_token" id="recaptcha_v3_token">

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">NIP / Username</label>
                <input type="text" name="nip" id="nip"
                       class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500 transition"
                       required autocomplete="off">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password"
                           class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500 transition"
                           style="padding-right: 48px;"
                           required>
                    <button type="button" onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition focus:outline-none"
                            tabindex="-1" aria-label="Lihat password">
                        <svg id="eye-login" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- reCAPTCHA v2 Checkbox -->
            <div class="flex justify-center py-1">
                <div class="g-recaptcha"
                     data-sitekey="<?= RECAPTCHA_V2_SITE_KEY ?>"
                     data-theme="dark"
                     data-size="normal"
                     id="recaptcha_v2_widget">
                </div>
            </div>

            <button type="submit" id="submitBtn"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-2xl font-semibold transition mt-2 shadow-lg disabled:opacity-60 disabled:cursor-not-allowed">
                LOGIN
            </button>
        </form>

        <a href="https://apelbaja.jatimprov.go.id/" target="_blank"
           class="block w-full mt-4 bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-2xl font-semibold transition shadow-lg text-center">
            APEL BAJA Tender
        </a>

        <!-- Badge TOS Google (wajib) -->
        <p class="text-center text-xs text-slate-500 mt-4">
            Dilindungi oleh reCAPTCHA &mdash;
            <a href="https://policies.google.com/privacy" target="_blank" class="underline hover:text-slate-300">Privasi</a> &amp;
            <a href="https://policies.google.com/terms"   target="_blank" class="underline hover:text-slate-300">Ketentuan</a> Google
        </p>

        <div class="text-center mt-6 flex flex-col gap-2">
            <a href="index.php?page=lupa_password" class="text-slate-400 hover:text-white transition text-sm">Lupa Password?</a>
            <a href="index.php?page=register"
               class="text-slate-400 hover:text-white text-sm transition">
                Belum punya akun? <span class="text-emerald-400 font-medium">Registrasi</span>
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-login');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.122-3.364M6.53 6.53A9.97 9.97 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.132 5.411M3 3l18 18"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
}

// ── Utility ──────────────────────────────────────────────────────
function showError(fieldId, msg) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.remove('border-white/20', 'focus:border-emerald-500');
    field.classList.add('border-red-500/70', 'focus:border-red-400');
    let err = document.getElementById(fieldId + '_err');
    if (!err) {
        err = document.createElement('div');
        err.id = fieldId + '_err';
        err.className = 'field-error';
        field.parentNode.insertBefore(err, field.nextSibling.nextSibling || null);
    }
    err.innerHTML = `<svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>${msg}`;
}
function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.remove('border-red-500/70', 'focus:border-red-400');
    field.classList.add('border-white/20', 'focus:border-emerald-500');
    const err = document.getElementById(fieldId + '_err');
    if (err) err.remove();
}

// ── Validasi Input ──────────────────────────────────────────────────
const nipInput = document.getElementById('nip');

nipInput.addEventListener('input', function () {
    const val = this.value.trim();
    if (val === '') showError('nip', 'NIP/Username wajib diisi.');
    else clearError('nip');
});
</script>

<script>
document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Validasi NIP
    const nipVal = nipInput.value.trim();
    if (nipVal === '') {
        showError('nip', 'NIP/Username wajib diisi.');
        return;
    }

    const btn = document.getElementById('submitBtn');

    // Pastikan v2 sudah dicentang sebelum lanjut
    const v2Response = grecaptcha.getResponse();
    if (!v2Response) {
        alert('Silakan centang "Saya bukan robot" terlebih dahulu.');
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Memverifikasi...';

    // Ambil token v3 lalu submit
    grecaptcha.ready(function () {
        grecaptcha.execute('<?= RECAPTCHA_V3_SITE_KEY ?>', { action: 'login' })
            .then(function (token) {
                document.getElementById('recaptcha_v3_token').value = token;
                document.getElementById('loginForm').submit();
            })
            .catch(function () {
                btn.disabled    = false;
                btn.textContent = 'LOGIN';
                alert('Gagal mendapatkan token reCAPTCHA v3. Periksa koneksi internet Anda.');
            });
    });
});
</script>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/auth.php';
?>

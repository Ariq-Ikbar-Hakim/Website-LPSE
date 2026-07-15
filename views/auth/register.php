<?php 
// Helper: class border input (merah jika error, default jika tidak)
if (!function_exists('inputClass')) {
    function inputClass(string $field, array $errors): string {
        $base = "w-full px-5 py-4 bg-white/10 border rounded-2xl text-white placeholder-slate-400 focus:outline-none transition";
        return $base . (isset($errors[$field])
            ? " border-red-500/70 focus:border-red-400"
            : " border-white/20 focus:border-emerald-500");
    }
}
ob_start(); 
?>

<!-- reCAPTCHA v3 Script -->
<script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_V3_SITE_KEY ?>"></script>

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

/* counter karakter NIP & telepon */
.char-counter { font-size: 0.72rem; color: #94a3b8; text-align: right; margin-top: 4px; }
.char-counter.ok  { color: #34d399; }
.char-counter.bad { color: #f87171; }
</style>

<div class="w-full max-w-xl">

    <!-- Logo -->
    <div class="flex justify-center items-center mb-8">
        <img src="assets/images/logo.png" alt="APEL BAJA Tender" class="h-20 w-auto drop-shadow-lg" onerror="this.style.display='none'">
        <div class="text-white text-3xl font-bold ml-3">APELBAJA</div>
    </div>

    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8">

        <div class="text-center mb-8">
            <h3 class="text-2xl font-semibold text-white">Registrasi Akun</h3>
            <p class="text-slate-400 mt-2">Daftar sebagai PPK atau PP</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-2xl mb-6 text-center">
                <?= htmlspecialchars($success_msg) ?>
            </div>
            <a href="index.php?page=login" class="block text-center bg-emerald-600 hover:bg-emerald-500 text-white py-3.5 rounded-2xl font-medium transition">
                Ke Halaman Login
            </a>
        <?php else: ?>

            <?php if (!empty($captcha_error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-2xl mb-6 text-center text-sm">
                    <?= htmlspecialchars($captcha_error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register" class="space-y-5" id="registerForm" novalidate>
                <?= csrfField() ?>
                <input type="hidden" name="recaptcha_v3_token" id="recaptcha_v3_token">

                <!-- NIP + No. Telepon -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            NIP <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="nip" id="nip"
                               inputmode="numeric" maxlength="18"
                               value="<?= htmlspecialchars($old['nip'] ?? '') ?>"
                               placeholder="18 digit angka"
                               class="<?= inputClass('nip', $errors) ?>"
                               required>
                        <div class="char-counter" id="nip_counter">0 / 18</div>
                        <?php if (isset($errors['nip'])): ?>
                            <div class="field-error">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <?= htmlspecialchars($errors['nip']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- No. Telepon -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            No. Telepon <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="no_telp" id="no_telp"
                               inputmode="numeric" maxlength="13"
                               value="<?= htmlspecialchars($old['no_telp'] ?? '') ?>"
                               placeholder="08xxxxxxxxxx"
                               class="<?= inputClass('no_telp', $errors) ?>"
                               required>
                        <div class="char-counter" id="telp_counter">0 / 10–13</div>
                        <?php if (isset($errors['no_telp'])): ?>
                            <div class="field-error">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <?= htmlspecialchars($errors['no_telp']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nama Lengkap & Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Nama Lengkap <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="nama" id="nama"
                               value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                               placeholder="Hanya huruf"
                               class="<?= inputClass('nama', $errors) ?>"
                               required>
                        <?php if (isset($errors['nama'])): ?>
                            <div class="field-error">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <?= htmlspecialchars($errors['nama']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Email <span class="text-red-400">*</span>
                        </label>
                        <input type="email" name="email" id="email"
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               placeholder="contoh@email.com"
                               class="<?= inputClass('email', $errors) ?>"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="field-error">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <?= htmlspecialchars($errors['email']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Jabatan & OPD -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Jabatan Pendaftaran -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Jabatan Pendaftaran <span class="text-red-400">*</span>
                        </label>
                        <select name="jabatan_aktif" id="jabatan_aktif"
                                class="<?= inputClass('jabatan_aktif', $errors) ?>"
                                required>
                            <option value="PPK" class="text-slate-800" <?= ($old['jabatan_aktif'] ?? '') === 'PPK' ? 'selected' : '' ?>>Pejabat Pembuat Komitmen (PPK)</option>
                            <option value="PP" class="text-slate-800" <?= ($old['jabatan_aktif'] ?? '') === 'PP' ? 'selected' : '' ?>>Pejabat Pengadaan (PP)</option>
                        </select>
                        <?php if (isset($errors['jabatan_aktif'])): ?>
                            <div class="field-error">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <?= htmlspecialchars($errors['jabatan_aktif']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- OPD -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            OPD / Unit Kerja <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="opd" id="opd"
                               value="<?= htmlspecialchars($old['opd'] ?? '') ?>"
                               placeholder="Nama OPD atau unit kerja"
                               class="<?= inputClass('opd', $errors) ?>"
                               required>
                        <?php if (isset($errors['opd'])): ?>
                            <div class="field-error">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <?= htmlspecialchars($errors['opd']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Password <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                               placeholder="Minimal 6 karakter"
                               class="<?= inputClass('password', $errors) ?>"
                               style="padding-right: 48px;"
                               required>
                        <button type="button" onclick="togglePassword('password', 'eye-register')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition focus:outline-none"
                                tabindex="-1" aria-label="Lihat password">
                            <svg id="eye-register" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="field-error">
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <?= htmlspecialchars($errors['password']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" id="submitBtn"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-2xl font-semibold transition mt-4 shadow-lg disabled:opacity-60 disabled:cursor-not-allowed">
                    DAFTAR SEKARANG
                </button>
            </form>

        <?php endif; ?>

        <p class="text-center text-xs text-slate-500 mt-4">
            Dilindungi oleh reCAPTCHA &mdash;
            <a href="https://policies.google.com/privacy" target="_blank" class="underline hover:text-slate-300">Privasi</a> &amp;
            <a href="https://policies.google.com/terms"   target="_blank" class="underline hover:text-slate-300">Ketentuan</a> Google
        </p>

        <div class="text-center mt-6">
            <a href="index.php?page=login" class="text-slate-400 hover:text-white text-sm transition">
                Sudah punya akun? <span class="text-emerald-400 font-medium">Login</span>
            </a>
        </div>
    </div>
</div>

<script>
// ── Toggle lihat/sembunyikan password ───────────────────────────
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
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

// ── Counter NIP ──────────────────────────────────────────────────
const nipInput     = document.getElementById('nip');
const nipCounter   = document.getElementById('nip_counter');
const telpInput    = document.getElementById('no_telp');
const telpCounter  = document.getElementById('telp_counter');

nipInput.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    const len = this.value.length;
    nipCounter.textContent = len + ' / 18';
    nipCounter.className = 'char-counter ' + (len === 18 ? 'ok' : (len > 0 ? 'bad' : ''));
    if (len > 0 && len < 18)       showError('nip', 'NIP harus tepat 18 digit.');
    else if (len === 0)             showError('nip', 'NIP wajib diisi.');
    else                            clearError('nip');
});

// ── Counter No. Telepon ──────────────────────────────────────────
telpInput.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    const len = this.value.length;
    telpCounter.textContent = len + ' / 10–13';
    const validLen  = len >= 10 && len <= 13;
    const validPfx  = this.value.substring(0, 2) === '08';
    telpCounter.className = 'char-counter ' + (validLen && validPfx ? 'ok' : (len > 0 ? 'bad' : ''));
    if (len === 0) {
        showError('no_telp', 'No. telepon wajib diisi.');
    } else if (!validLen) {
        showError('no_telp', 'No. telepon harus 10–13 digit (sekarang ' + len + ' digit).');
    } else if (!validPfx) {
        showError('no_telp', 'No. telepon harus diawali 08.');
    } else {
        clearError('no_telp');
    }
});

// ── Validasi nama real-time ──────────────────────────────────────
document.getElementById('nama').addEventListener('input', function () {
    this.value = this.value.replace(/[^a-zA-Z\s'\-\.]/g, '');
    const val = this.value.trim();
    if (val === '') {
        showError('nama', 'Nama lengkap wajib diisi.');
    } else {
        clearError('nama');
    }
});

// ── Validasi email real-time (blur) ─────────────────────────────
const EMAIL_REGEX = /^[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*@[a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]+)*\.[a-zA-Z]{2,}$/;
document.getElementById('email').addEventListener('blur', function () {
    const val = this.value.trim();
    if (val === '') {
        showError('email', 'Email wajib diisi.');
    } else if (!val.includes('@')) {
        showError('email', 'Email harus mengandung simbol @.');
    } else {
        const [user] = val.split('@');
        if (/[^a-zA-Z0-9.]/.test(user)) {
            showError('email', 'Username email hanya boleh huruf, angka, dan titik. Simbol lain tidak diizinkan.');
        } else if (/^\.|\.\.|\.$/. test(user)) {
            showError('email', 'Titik tidak boleh di awal, akhir, atau berturut-turut (contoh: a..b).');
        } else if (!EMAIL_REGEX.test(val)) {
            showError('email', 'Format tidak valid. Contoh: nama@gmail.com atau nama.a@yahoo.com');
        } else {
            clearError('email');
        }
    }
});

// ── Validasi OPD real-time ───────────────────────────────────────
document.getElementById('opd').addEventListener('input', function () {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
    const val = this.value.trim();
    if (val === '') {
        showError('opd', 'OPD / Unit Kerja wajib diisi.');
    } else {
        clearError('opd');
    }
});

// ── Validasi password: wajib + min 6 karakter (blur) ────────────
document.getElementById('password').addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError('password', 'Password wajib diisi.');
    } else if (this.value.length < 6) {
        showError('password', 'Password minimal 6 karakter!');
    } else {
        clearError('password');
    }
});

// ── Validasi jabatan (blur) ────────────
document.getElementById('jabatan_aktif').addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError('jabatan_aktif', 'Jabatan pendaftaran wajib diisi.');
    } else {
        clearError('jabatan_aktif');
    }
});

// ── Submit: validasi final → reCAPTCHA v3 → kirim ───────────────
document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const fields = {
        nip: { val: nipInput.value.trim(), checks: [
            [v => v === '',        'NIP wajib diisi.'],
            [v => /\D/.test(v),    'NIP hanya boleh angka.'],
            [v => v.length !== 18, 'NIP harus tepat 18 digit.'],
        ]},
        no_telp: { val: telpInput.value.trim(), checks: [
            [v => v === '',                          'No. telepon wajib diisi.'],
            [v => /\D/.test(v),                      'No. telepon hanya boleh angka.'],
            [v => v.length < 10 || v.length > 13,    'No. telepon harus 10–13 digit.'],
            [v => v.substring(0,2) !== '08',         'No. telepon harus diawali 08.'],
        ]},
        nama: { val: document.getElementById('nama').value.trim(), checks: [
            [v => v === '',                         'Nama lengkap wajib diisi.'],
            [v => /\d/.test(v),                    'Nama tidak boleh mengandung angka.'],
            [v => /[^a-zA-Z\s'\-\.]/.test(v),     'Nama tidak boleh mengandung simbol (contoh: ! @ # $ % ^ & * dll.).'],
        ]},
        email: { val: document.getElementById('email').value.trim(), checks: [
            [v => v === '',          'Email wajib diisi.'],
            [v => !v.includes('@'),  'Email harus mengandung simbol @.'],
            [v => { const u = v.split('@')[0]; return /[^a-zA-Z0-9.]/.test(u); },
                                     'Username email hanya boleh huruf, angka, dan titik.'],
            [v => { const u = v.split('@')[0]; return /^\.|\.\.|\.$/. test(u); },
                                     'Titik tidak boleh di awal, akhir, atau berturut-turut.'],
            [v => !EMAIL_REGEX.test(v), 'Format tidak valid. Contoh: nama@gmail.com'],
        ]},
        opd: { val: document.getElementById('opd').value.trim(), checks: [
            [v => v === '',                     'OPD / Unit Kerja wajib diisi.'],
            [v => /\d/.test(v),                'OPD tidak boleh mengandung angka.'],
            [v => /[^a-zA-Z\s]/.test(v),       'OPD tidak boleh mengandung simbol (contoh: ! @ # $ % ^ & * dll.).'],
        ]},
        jabatan_aktif: { val: document.getElementById('jabatan_aktif').value.trim(), checks: [
            [v => v === '',                     'Jabatan pendaftaran wajib diisi.'],
        ]},
        password: { val: document.getElementById('password').value, checks: [
            [v => v === '',      'Password wajib diisi.'],
            [v => v.length < 6,  'Password minimal 6 karakter!'],
        ]},
    };

    let valid = true;
    for (const [id, cfg] of Object.entries(fields)) {
        let fieldOk = true;
        for (const [test, msg] of cfg.checks) {
            if (test(cfg.val)) { showError(id, msg); fieldOk = false; valid = false; break; }
        }
        if (fieldOk) clearError(id);
    }

    if (!valid) {
        const firstErr = document.querySelector('.field-error');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    const btn = document.getElementById('submitBtn');
    btn.disabled    = true;
    btn.textContent = 'Memverifikasi...';

    grecaptcha.ready(function () {
        grecaptcha.execute('<?= RECAPTCHA_V3_SITE_KEY ?>', { action: 'register' })
            .then(function (token) {
                document.getElementById('recaptcha_v3_token').value = token;
                document.getElementById('registerForm').submit();
            })
            .catch(function () {
                btn.disabled    = false;
                btn.textContent = 'DAFTAR SEKARANG';
                alert('Gagal mendapatkan token reCAPTCHA. Periksa koneksi internet Anda.');
            });
    });
});
</script>

<?php 
$content = ob_get_clean();
require BASEPATH . '/views/layouts/auth.php';
?>

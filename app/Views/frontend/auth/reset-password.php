<?php
if (!function_exists('getLuminaImage')) {
    $fn = defined('BASE_PATH') ? BASE_PATH . '/includes/functions.php' : __DIR__ . '/../../../includes/functions.php';
    if (is_file($fn)) require_once $fn;
}
$baseUrl = $baseUrl ?? '';
$errors = $errors ?? [];
$old = $old ?? [];
$token = $token ?? '';
$heroImage = function_exists('getLuminaImage') ? getLuminaImage('hero', 4) : '';
?>
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    <!-- Sol Kolon: Görsel -->
    <div class="hidden lg:block relative">
        <?php if ($heroImage): ?>
            <img src="<?= htmlspecialchars($heroImage) ?>" alt="" class="h-full w-full object-cover min-h-screen">
        <?php else: ?>
            <div class="h-full w-full min-h-screen bg-gray-200"></div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
            <span class="text-4xl font-display tracking-[0.5em] text-white">LUMINA</span>
        </div>
    </div>

    <!-- Sağ Kolon: Form -->
    <div class="flex flex-col justify-center px-8 md:px-20 lg:px-32 py-12 bg-white">
        <a href="<?= htmlspecialchars($baseUrl) ?>/giris" class="text-xs text-gray-500 hover:text-black mb-8 block transition">&lt; Giriş Yap</a>

        <h1 class="text-2xl font-display tracking-wide text-gray-900 mb-2">YENİ ŞİFRE OLUŞTUR</h1>
        <p class="text-sm text-gray-500 mb-8">Lütfen hesabınız için yeni ve güçlü bir şifre belirleyin.</p>

        <?php if (!empty($errors)): ?>
            <ul class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 list-none space-y-1">
                <?php foreach (is_array($errors) ? $errors : [$errors] as $err): ?>
                    <li><?= htmlspecialchars(is_string($err) ? $err : '') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/sifre-sifirla<?= $token ? '?token=' . htmlspecialchars($token) : '' ?>" class="space-y-4" x-data="resetPasswordForm()" @submit.prevent="submitForm()">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <!-- Yeni Şifre + Şifre gücü kuralları (register ile aynı) -->
            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-medium text-gray-600 mb-2">Yeni Şifre</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" x-model="password" @input="checkStrength()" placeholder="••••••••" minlength="8" class="w-full border border-gray-300 rounded-md py-3 px-4 pr-12 text-sm placeholder-gray-400 focus:border-black focus:ring-black transition" autocomplete="new-password">
                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" :aria-label="show ? 'Şifreyi gizle' : 'Şifreyi göster'">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                <ul class="text-[10px] mt-3 space-y-1">
                    <li class="flex items-center gap-2" :class="rules.length ? 'text-green-600' : 'text-gray-400'">
                        <span x-show="rules.length" class="text-green-600">✓</span>
                        <span x-show="!rules.length" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                        En az 8 karakter
                    </li>
                    <li class="flex items-center gap-2" :class="rules.upper ? 'text-green-600' : 'text-gray-400'">
                        <span x-show="rules.upper" class="text-green-600">✓</span>
                        <span x-show="!rules.upper" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                        En az bir büyük harf
                    </li>
                    <li class="flex items-center gap-2" :class="rules.number ? 'text-green-600' : 'text-gray-400'">
                        <span x-show="rules.number" class="text-green-600">✓</span>
                        <span x-show="!rules.number" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                        En az bir rakam
                    </li>
                    <li class="flex items-center gap-2" :class="rules.special ? 'text-green-600' : 'text-gray-400'">
                        <span x-show="rules.special" class="text-green-600">✓</span>
                        <span x-show="!rules.special" x-cloak class="w-3 h-3 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
                        En az bir sembol (!@#$% vb.)
                    </li>
                </ul>
            </div>

            <div>
                <label for="password_confirm" class="block text-xs font-medium text-gray-600 mb-2">Şifre Tekrar</label>
                <input type="password" id="password_confirm" name="password_confirm" x-model="password_confirm" placeholder="••••••••" required class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm placeholder-gray-400 focus:border-black focus:ring-black transition" autocomplete="new-password">
            </div>

            <button type="submit" class="w-full bg-black text-white py-4 uppercase tracking-widest text-xs font-bold hover:bg-gray-800 transition rounded-md disabled:opacity-50 disabled:cursor-not-allowed" :disabled="!canSubmit()">
                ŞİFREYİ GÜNCELLE
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('resetPasswordForm', function() {
        var baseUrl = <?= json_encode($baseUrl) ?>;
        return {
            password: '',
            password_confirm: '',
            rules: { length: false, upper: false, number: false, special: false },
            checkStrength: function() {
                var el = document.getElementById('password');
                if (!el) return;
                this.password = el.value;
                this.rules.length = this.password.length >= 8;
                this.rules.upper = /[A-Z]/.test(this.password);
                this.rules.number = /[0-9]/.test(this.password);
                this.rules.special = /[^A-Za-z0-9]/.test(this.password);
            },
            allRulesPass: function() {
                return this.rules.length && this.rules.upper && this.rules.number && this.rules.special;
            },
            canSubmit: function() {
                return this.allRulesPass() && this.password_confirm.length > 0 && this.password === this.password_confirm;
            },
            submitForm: function() {
                if (!this.allRulesPass()) return;
                if (this.password !== this.password_confirm) {
                    alert('Şifreler eşleşmiyor.');
                    return;
                }
                alert('Şifreniz güncellendi');
                window.location = baseUrl + '/giris';
            }
        };
    });
});
</script>

<?php
if (!function_exists('getLuminaImage')) {
    $fn = defined('BASE_PATH') ? BASE_PATH . '/includes/functions.php' : __DIR__ . '/../../../includes/functions.php';
    if (is_file($fn)) require_once $fn;
}
$baseUrl = $baseUrl ?? '';
$old = $old ?? [];
$errors = $errors ?? [];
$redirect = $redirect ?? '';
$heroImage = function_exists('getLuminaImage') ? getLuminaImage('hero', 3) : '';
?>
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    <!-- Sol Kolon: Görsel -->
    <div class="relative hidden lg:block">
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
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-xs text-gray-500 hover:text-black mb-8 block transition">← Ana Sayfaya Dön</a>

        <h1 class="text-2xl font-display tracking-wide mb-2 text-gray-900">TEKRAR HOŞGELDİNİZ</h1>
        <p class="text-sm text-gray-500 mb-8">Hesabınıza giriş yapın.</p>

        <?php if (!empty($errors)): ?>
            <ul class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 list-none space-y-1">
                <?php foreach (is_array($errors) ? $errors : [$errors] as $err): ?>
                    <li><?= htmlspecialchars(is_string($err) ? $err : (is_array($err) ? implode(' ', $err) : '')) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/giris" class="space-y-4">
            <?php if ($redirect !== ''): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <?php endif; ?>

            <div>
                <label for="email" class="block text-xs font-medium text-gray-600 mb-2">E-posta</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required placeholder="ornek@email.com" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm placeholder-gray-400 focus:ring-2 focus:ring-black focus:border-black transition">
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-medium text-gray-600 mb-2">Şifre</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" required placeholder="••••••••" class="w-full border border-gray-300 rounded-md py-3 px-4 pr-12 text-sm placeholder-gray-400 focus:ring-2 focus:ring-black focus:border-black transition">
                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" :aria-label="show ? 'Şifreyi gizle' : 'Şifreyi göster'">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" <?= !empty($old['remember']) ? 'checked' : '' ?> class="w-4 h-4 border-gray-300 rounded text-black focus:ring-black">
                    <span class="text-sm text-gray-600">Beni Hatırla</span>
                </label>
                <a href="<?= htmlspecialchars($baseUrl) ?>/sifremi-unuttum" class="text-xs text-gray-500 hover:text-black underline transition">Şifremi Unuttum</a>
            </div>

            <button type="submit" class="w-full bg-black text-white py-4 mt-6 uppercase tracking-widest text-xs font-bold hover:bg-gray-800 transition rounded-md">
                GİRİŞ YAP
            </button>
        </form>

        <p class="text-center mt-6 text-xs text-gray-500">
            Hesabınız yok mu? <a href="<?= htmlspecialchars($baseUrl) ?>/kayit" class="underline hover:text-black transition">Kayıt Olun</a>
        </p>

        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-center text-xs text-gray-400 mb-4">Veya</p>
            <div class="flex gap-3 justify-center">
                <button type="button" class="flex items-center justify-center w-12 h-12 border border-gray-300 rounded-md hover:border-gray-400 transition" aria-label="Google ile giriş yap">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                </button>
                <button type="button" class="flex items-center justify-center w-12 h-12 border border-gray-300 rounded-md hover:border-gray-400 transition" aria-label="Apple ile giriş yap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13 2.9 1.4 4.08-.6 5.08-1.9 1.01-1.39 1.79-2.9 1.79-2.9s.49 2.12-.11 3.06zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
if (!function_exists('getLuminaImage')) {
    $fn = defined('BASE_PATH') ? BASE_PATH . '/includes/functions.php' : __DIR__ . '/../../../includes/functions.php';
    if (is_file($fn)) require_once $fn;
}
$baseUrl = $baseUrl ?? '';
$errors = $errors ?? [];
$old = $old ?? [];
$showSuccess = $showSuccess ?? false;
$heroImage = function_exists('getLuminaImage') ? getLuminaImage('hero', 4) : '';
?>
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2" x-data="{ state: '<?= $showSuccess ? 'success' : 'form' ?>' }">
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

    <!-- Sağ Kolon: İçerik -->
    <div class="flex flex-col justify-center px-8 md:px-20 lg:px-32 py-12 bg-white">
        <!-- DURUM A: Form -->
        <div x-show="state === 'form'" x-cloak class="space-y-6">
            <a href="<?= htmlspecialchars($baseUrl) ?>/giris" class="text-xs text-gray-500 hover:text-black mb-8 block transition">&lt; Giriş Yap</a>

            <h1 class="text-2xl font-display tracking-wide text-gray-900">ŞİFRENİZİ SIFIRLAYIN</h1>
            <p class="text-sm text-gray-500 mb-8">Hesabınıza ait e-posta adresinizi girin, size şifre sıfırlama talimatlarını gönderelim.</p>

            <?php if (!empty($errors)): ?>
                <ul class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 list-none space-y-1">
                    <?php foreach (is_array($errors) ? $errors : [$errors] as $err): ?>
                        <li><?= htmlspecialchars(is_string($err) ? $err : '') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/sifremi-unuttum" class="space-y-4" @submit.prevent="state = 'success'">
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-600 mb-2">E-posta Adresi</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required placeholder="ornek@email.com" class="w-full border border-gray-300 rounded-md py-3 px-4 text-sm placeholder-gray-400 focus:border-black focus:ring-black transition">
                </div>
                <button type="submit" class="w-full bg-black text-white py-4 uppercase tracking-widest text-xs font-bold hover:bg-gray-800 transition rounded-md">
                    TALİMATLARI GÖNDER
                </button>
            </form>
        </div>

        <!-- DURUM B: Başarılı mesaj (Alpine ile sayfa yenilenmeden; POST sonrası backend state ile de gösterilebilir) -->
        <div x-show="state === 'success'" x-cloak class="space-y-6 text-center">
            <div class="flex justify-center">
                <svg class="w-20 h-20 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-display tracking-wide text-gray-900">E-POSTA GÖNDERİLDİ</h1>
            <p class="text-sm text-gray-500">Lütfen gelen kutunuzu kontrol edin. Şifrenizi sıfırlamanız için bir bağlantı gönderdik.</p>
            <a href="<?= htmlspecialchars($baseUrl) ?>/giris" class="inline-block w-full py-4 border-2 border-black text-black uppercase tracking-widest text-xs font-bold hover:bg-black hover:text-white transition rounded-md text-center">
                Giriş sayfasına dön
            </a>
        </div>
    </div>
</div>

<?php
if (!function_exists('getLuminaImage')) {
    $fn = defined('BASE_PATH') ? BASE_PATH . '/includes/functions.php' : dirname(__DIR__, 3) . '/includes/functions.php';
    if (is_file($fn)) require_once $fn;
}
$baseUrl = $baseUrl ?? '';
$heroImage = function_exists('getLuminaImage') ? getLuminaImage('hero', 1) : '';
$imgStory1 = function_exists('getLuminaImage') ? getLuminaImage('product', 2) : '';
$imgStory2 = function_exists('getLuminaImage') ? getLuminaImage('hero', 3) : '';
?>
<style>
.about-hero-image { filter: grayscale(0.2); }
</style>

<!-- 1. Hero Bölümü -->
<div class="max-w-[1200px] mx-auto px-6 pt-12 pb-6">
    <?php if ($heroImage): ?>
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="" class="w-full h-[500px] object-cover object-center about-hero-image rounded-sm">
    <?php else: ?>
        <div class="w-full h-[500px] bg-gray-200 rounded-sm flex items-center justify-center text-gray-400 text-sm">Görsel</div>
    <?php endif; ?>
    <div class="text-center mt-12 mb-16">
        <h1 class="font-display text-4xl tracking-widest uppercase text-primary mt-12 mb-6">MODANIN YAVAŞ VE ZAMANSIZ HALİ</h1>
        <p class="text-xl font-light text-gray-600 max-w-2xl mx-auto text-center leading-relaxed">Lumina, trendlerin ötesinde, kalite ve zarafetin buluştuğu noktadır. Biz sadece giysi değil, bir yaşam biçimi tasarlıyoruz.</p>
    </div>
</div>

<!-- 2. Hikaye Bölümü (Zig-Zag) -->
<div class="max-w-[1200px] mx-auto px-6 mb-24">
    <!-- Bölüm 1: Sol metin, sağ görsel -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center mb-24 lg:mb-32">
        <div class="order-2 lg:order-1 flex flex-col justify-center">
            <h2 class="font-display text-2xl tracking-widest uppercase text-primary mb-6">Köklerimiz</h2>
            <p class="text-gray-600 leading-relaxed mb-4">Lumina, 2014 yılında İstanbul’da, modanın hızlı tüketimine karşı duran bir atölye hayaliyle kuruldu. Kurucumuz, tekstil atölyelerinde geçen yılların ardından “daha az ama daha iyi” felsefesiyle yola çıktı.</p>
            <p class="text-gray-600 leading-relaxed">Her parça, doğal kumaşlar ve yerel zanaatkârlarla çalışarak tasarlanıyor. Trendler değil, zamansız kesimler ve kalıcı dokular bizi tanımlıyor.</p>
        </div>
        <div class="order-1 lg:order-2">
            <?php if ($imgStory1): ?>
                <img src="<?= htmlspecialchars($imgStory1) ?>" alt="Atölye detayı" class="w-full aspect-[4/5] object-cover object-center rounded-sm">
            <?php else: ?>
                <div class="w-full aspect-[4/5] bg-gray-100 rounded-sm flex items-center justify-center text-gray-400 text-sm">Görsel</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bölüm 2: Sol görsel, sağ metin -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
        <div>
            <?php if ($imgStory2): ?>
                <img src="<?= htmlspecialchars($imgStory2) ?>" alt="Doğal doku" class="w-full aspect-[4/5] object-cover object-center rounded-sm">
            <?php else: ?>
                <div class="w-full aspect-[4/5] bg-gray-100 rounded-sm flex items-center justify-center text-gray-400 text-sm">Görsel</div>
            <?php endif; ?>
        </div>
        <div class="flex flex-col justify-center">
            <h2 class="font-display text-2xl tracking-widest uppercase text-primary mb-6">Sürdürülebilirlik</h2>
            <p class="text-gray-600 leading-relaxed mb-4">Üretimde doğal ve geri dönüştürülebilir malzemeleri tercih ediyoruz. Atık su ve enerji kullanımını azaltan atölyelerle çalışıyor, her koleksiyonda “kim yaptı?” sorusuna şeffaf yanıt veriyoruz.</p>
            <p class="text-gray-600 leading-relaxed">Lumina’da satın aldığınız her parça, hem size hem gezegene saygılı bir seçimdir.</p>
        </div>
    </div>
</div>

<!-- 3. Değerlerimiz -->
<section class="bg-gray-50 py-24">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 md:gap-12">
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2c-2 5-5 8-5 12a5 5 0 0010 0c0-4-3-7-5-12zM12 2c2 5 5 8 5 12a5 5 0 01-10 0c0-4 3-7 5-12zM12 6v10"/>
                    </svg>
                </div>
                <h3 class="text-xs font-bold tracking-widest uppercase text-primary mb-4">DOĞAL İÇERİK</h3>
                <p class="text-sm text-gray-600 leading-relaxed max-w-xs mx-auto">Pamuk, keten, yün ve geri dönüştürülmüş lifler. Sentetik kullanımımız minimumda; her kumaş seçiminde çevre ve cilt dostu içerik önceliğimizdir.</p>
            </div>
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M12 3l2 3M12 3l-2 3M12 21l-2-4M12 21l2-4"/>
                        <circle cx="12" cy="3" r="1.5" stroke="currentColor" stroke-width="1.2" fill="none"/>
                    </svg>
                </div>
                <h3 class="text-xs font-bold tracking-widest uppercase text-primary mb-4">EL İŞÇİLİĞİ</h3>
                <p class="text-sm text-gray-600 leading-relaxed max-w-xs mx-auto">Her ürün, deneyimli atölyelerde dikilir ve kontrol edilir. Seri üretim hızına değil, detaylara ve dayanıklılığa yatırım yapıyoruz.</p>
            </div>
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <h3 class="text-xs font-bold tracking-widest uppercase text-primary mb-4">ŞEFFAFLIK</h3>
                <p class="text-sm text-gray-600 leading-relaxed max-w-xs mx-auto">Nerede ve kim tarafından üretildiğini açıkça paylaşıyoruz. Müşterimizle güven ilişkisi, şeffaflıkla başlar.</p>
            </div>
        </div>
    </div>
</section>

<!-- 4. Rakamlarla Lumina -->
<section class="border-t border-gray-200 py-16">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-12 text-center">
            <div>
                <p class="font-display text-3xl md:text-4xl tracking-tight text-primary mb-1">10+</p>
                <p class="text-xs font-bold tracking-widest uppercase text-gray-500">Yıl</p>
            </div>
            <div>
                <p class="font-display text-3xl md:text-4xl tracking-tight text-primary mb-1">50+</p>
                <p class="text-xs font-bold tracking-widest uppercase text-gray-500">Zanaatkar</p>
            </div>
            <div>
                <p class="font-display text-3xl md:text-4xl tracking-tight text-primary mb-1">%100</p>
                <p class="text-xs font-bold tracking-widest uppercase text-gray-500">Sürdürülebilir</p>
            </div>
        </div>
    </div>
</section>

<div class="max-w-[1200px] mx-auto px-6 py-12 text-center">
    <a href="<?= htmlspecialchars($baseUrl) ?>/iletisim" class="inline-block font-display text-sm tracking-widest uppercase text-primary border-b border-primary pb-1 hover:opacity-70 transition">İletişime geçin</a>
</div>

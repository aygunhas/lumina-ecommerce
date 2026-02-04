<?php
if (!function_exists('getLuminaImage')) {
    require_once __DIR__ . '/functions.php';
}
$baseUrl = $baseUrl ?? '';
$imgYeniSezon = getLuminaImage('hero', 0);
$imgKadin = getLuminaImage('product', 0);
$imgErkek = getLuminaImage('product', 3);
$aksesuarLocalPath = dirname(__DIR__) . '/public/assets/images/aksesuar.jpg';
$imgAksesuar = (file_exists($aksesuarLocalPath)) ? ($baseUrl . '/assets/images/aksesuar.jpg') : getLuminaImage('hero', 1);
$saleLocalPath = dirname(__DIR__) . '/public/assets/images/sale.jpg';
$imgSale = (file_exists($saleLocalPath)) ? ($baseUrl . '/assets/images/sale.jpg') : getLuminaImage('sale', 0);
?>
<section class="overflow-x-hidden">
    <div class="grid gap-2 mx-auto max-w-[1400px] px-4 md:px-6 py-12 grid-cols-2 md:grid-cols-4 md:grid-rows-2 md:h-[600px]">
        <!-- 1. YENİ SEZON -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/new-arrivals.php" class="relative group overflow-hidden w-full col-span-2 h-[300px] md:col-span-2 md:row-span-2 md:h-auto block">
            <img src="<?= htmlspecialchars($imgYeniSezon) ?>" alt="Yeni Sezon" class="object-cover w-full h-full transition duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
            <div class="absolute bottom-4 left-4 md:bottom-6 md:left-6">
                <span class="font-display tracking-widest text-white uppercase text-3xl md:text-4xl block group-hover:-translate-y-1 transition">YENİ SEZON</span>
                <span class="font-display tracking-widest text-white/90 uppercase text-sm mt-1 block group-hover:-translate-y-1 transition">Koleksiyonu Keşfet</span>
            </div>
        </a>

        <!-- 2. KADIN -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/women.php" class="relative group overflow-hidden w-full col-span-1 h-[200px] md:col-span-1 md:row-span-1 md:h-auto block">
            <img src="<?= htmlspecialchars($imgKadin) ?>" alt="Kadın" class="object-cover w-full h-full transition duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
            <span class="absolute bottom-4 left-4 md:bottom-6 md:left-6 font-display tracking-widest text-white uppercase text-2xl md:text-3xl group-hover:-translate-y-1 transition">KADIN</span>
        </a>

        <!-- 3. ERKEK -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/men.php" class="relative group overflow-hidden w-full col-span-1 h-[200px] md:col-span-1 md:row-span-1 md:h-auto block">
            <img src="<?= htmlspecialchars($imgErkek) ?>" alt="Erkek" class="object-cover w-full h-full transition duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
            <span class="absolute bottom-4 left-4 md:bottom-6 md:left-6 font-display tracking-widest text-white uppercase text-2xl md:text-3xl group-hover:-translate-y-1 transition">ERKEK</span>
        </a>

        <!-- 4. AKSESUAR -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/accessories.php" class="relative group overflow-hidden w-full col-span-1 h-[200px] md:col-span-1 md:row-span-1 md:h-auto block">
            <img src="<?= htmlspecialchars($imgAksesuar) ?>" alt="Aksesuar" class="object-cover w-full h-full transition duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
            <span class="absolute bottom-4 left-4 md:bottom-6 md:left-6 font-display tracking-widest text-white uppercase text-2xl md:text-3xl group-hover:-translate-y-1 transition">AKSESUAR</span>
        </a>

        <!-- 5. SALE -->
        <a href="<?= htmlspecialchars($baseUrl) ?>/sale.php" class="relative group overflow-hidden w-full col-span-1 h-[200px] md:col-span-1 md:row-span-1 md:h-auto block">
            <img src="<?= htmlspecialchars($imgSale) ?>" alt="İndirim" class="object-cover w-full h-full transition duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
            <span class="absolute bottom-4 left-4 md:bottom-6 md:left-6 font-display tracking-widest text-white uppercase text-2xl md:text-3xl group-hover:-translate-y-1 transition inline-flex items-center gap-1">SALE <span class="text-lg">%</span></span>
        </a>
    </div>
</section>

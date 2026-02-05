<?php
if (!function_exists('getLuminaImage')) {
    require_once __DIR__ . '/functions.php';
}
$baseUrl = $baseUrl ?? '';
$categories = $categories ?? [];
$imgYeniSezon = getLuminaImage('hero', 0);
$imgKadin = getLuminaImage('product', 0);
$imgErkek = getLuminaImage('product', 3);
$aksesuarLocalPath = dirname(__DIR__) . '/public/assets/images/aksesuar.jpg';
$imgAksesuar = (file_exists($aksesuarLocalPath)) ? ($baseUrl . '/assets/images/aksesuar.jpg') : getLuminaImage('hero', 1);
$saleLocalPath = dirname(__DIR__) . '/public/assets/images/sale.jpg';
$imgSale = (file_exists($saleLocalPath)) ? ($baseUrl . '/assets/images/sale.jpg') : getLuminaImage('sale', 0);
$categoryImages = [$imgYeniSezon, $imgKadin, $imgErkek, $imgAksesuar, $imgSale];
?>
<section class="overflow-x-hidden">
    <div class="grid gap-2 mx-auto max-w-[1400px] px-4 md:px-6 py-12 grid-cols-2 md:grid-cols-4 md:rows-2 md:h-[600px]">
        <?php
        $take = 5;
        $cats = array_slice($categories, 0, $take);
        if (empty($cats)):
            ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/kategori/giyim" class="relative group overflow-hidden w-full col-span-2 h-[300px] md:col-span-2 md:row-span-2 md:h-auto block">
                <img src="<?= htmlspecialchars($imgYeniSezon) ?>" alt="Kategoriler" class="object-cover w-full h-full transition duration-700 group-hover:scale-105" />
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
                <div class="absolute bottom-4 left-4 md:bottom-6 md:left-6">
                    <span class="font-display tracking-widest text-white uppercase text-3xl md:text-4xl block group-hover:-translate-y-1 transition">KATEGORİLER</span>
                    <span class="font-display tracking-widest text-white/90 uppercase text-sm mt-1 block group-hover:-translate-y-1 transition">Ürünleri keşfet</span>
                </div>
            </a>
        <?php else:
            foreach ($cats as $i => $cat):
                $slug = $cat['slug'] ?? '';
                $name = $cat['name'] ?? '';
                $url = $slug ? ($baseUrl . '/kategori/' . htmlspecialchars($slug)) : '#';
                $img = $categoryImages[$i % count($categoryImages)] ?? $categoryImages[0];
                $label = mb_strtoupper($name);
                $isFirst = ($i === 0);
                ?>
                <a href="<?= htmlspecialchars($url) ?>" class="relative group overflow-hidden w-full <?= $isFirst ? 'col-span-2 h-[300px] md:col-span-2 md:row-span-2 md:h-auto' : 'col-span-1 h-[200px] md:col-span-1 md:row-span-1 md:h-auto' ?> block">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" class="object-cover w-full h-full transition duration-700 group-hover:scale-105" />
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
                    <div class="absolute bottom-4 left-4 md:bottom-6 md:left-6">
                        <?php if ($isFirst): ?>
                            <span class="font-display tracking-widest text-white uppercase text-3xl md:text-4xl block group-hover:-translate-y-1 transition"><?= htmlspecialchars($label) ?></span>
                            <span class="font-display tracking-widest text-white/90 uppercase text-sm mt-1 block group-hover:-translate-y-1 transition">Koleksiyonu Keşfet</span>
                        <?php else: ?>
                            <span class="font-display tracking-widest text-white uppercase text-2xl md:text-3xl group-hover:-translate-y-1 transition"><?= htmlspecialchars($label) ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach;
        endif; ?>
    </div>
</section>

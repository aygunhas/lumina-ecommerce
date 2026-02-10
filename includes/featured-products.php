<?php
if (!function_exists('getLuminaImage')) {
    require_once __DIR__ . '/functions.php';
}
$baseUrl = $baseUrl ?? '';
$productImages = $productImages ?? [];
$productHasVariants = $productHasVariants ?? [];
$productColorImages = $productColorImages ?? [];
$productColorVariants = $productColorVariants ?? [];
$featuredProducts = $featuredProducts ?? [];

// Veritabanından gelen öne çıkan ürünler varsa onları kullan (link /urun/slug), yoksa demo liste
if (!empty($featuredProducts)) {
    $itemsForGrid = $featuredProducts;
    $useRealProducts = true;
} else {
    $featuredItems = [
        ['name' => 'İpek Karışımlı Midi Elbise', 'price' => '2.450,00', 'colors' => ['#0a0a0a', '#c4a77d', '#8b7355']],
        ['name' => 'Oversize Yün Palto', 'price' => '3.890,00', 'colors' => ['#1a1a1a', '#4a4a4a', '#e8e0d8']],
        ['name' => 'Yüksek Bel Pantolon', 'price' => '1.650,00', 'colors' => ['#2c2c2c', '#5c4033', '#ffffff']],
        ['name' => 'Kaşmir Karışımlı Kazak', 'price' => '1.290,00', 'colors' => ['#3d3d3d', '#8b4513', '#d2b48c']],
        ['name' => 'Saten Bluz', 'price' => '890,00', 'colors' => ['#ffffff', '#1a1a1a', '#c4a77d']],
        ['name' => 'Deri Çanta', 'price' => '2.190,00', 'colors' => ['#0a0a0a', '#5c4033', '#8b7355']],
        ['name' => 'Yün Blend Triko', 'price' => '1.450,00', 'colors' => ['#4a4a4a', '#c4a77d', '#1a1a1a']],
        ['name' => 'Kadife Midi Etek', 'price' => '1.190,00', 'colors' => ['#2c2c2c', '#5c4033', '#8b7355']],
        ['name' => 'Oversize Gömlek', 'price' => '990,00', 'colors' => ['#ffffff', '#e8e0d8', '#1a1a1a']],
        ['name' => 'Deri Ceket', 'price' => '4.290,00', 'colors' => ['#0a0a0a', '#5c4033', '#3d3d3d']],
        ['name' => 'İpek Fular', 'price' => '490,00', 'colors' => ['#c4a77d', '#8b7355', '#1a1a1a']],
        ['name' => 'Pamuklu Basic Tişört', 'price' => '450,00', 'colors' => ['#ffffff', '#0a0a0a', '#4a4a4a']],
        ['name' => 'Dantel Detaylı Bluz', 'price' => '1.350,00', 'colors' => ['#ffffff', '#e8e0d8', '#1a1a1a']],
        ['name' => 'Yüksek Bel Midi Pantolon', 'price' => '1.790,00', 'colors' => ['#0a0a0a', '#2c2c2c', '#5c4033']],
        ['name' => 'Örme Hırka', 'price' => '1.590,00', 'colors' => ['#8b7355', '#c4a77d', '#3d3d3d']],
        ['name' => 'Saten Midi Etek', 'price' => '1.090,00', 'colors' => ['#1a1a1a', '#4a4a4a', '#c4a77d']],
        ['name' => 'Klasik Trench', 'price' => '3.490,00', 'colors' => ['#5c4033', '#0a0a0a', '#8b7355']],
        ['name' => 'Minimalist Kolye', 'price' => '690,00', 'colors' => ['#8b7355', '#1a1a1a', '#c4a77d']],
        ['name' => 'Yün Şal', 'price' => '790,00', 'colors' => ['#c4a77d', '#5c4033', '#2c2c2c']],
        ['name' => 'Deri Kemer', 'price' => '590,00', 'colors' => ['#0a0a0a', '#5c4033', '#8b7355']],
    ];
    $itemsForGrid = $featuredItems;
    $useRealProducts = false;
}
$totalCount = count($itemsForGrid);
$initialLimit = min(10, $totalCount);
?>
<section class="py-20" x-data="{ limit: <?= $initialLimit ?>, total: <?= $totalCount ?> }">
    <h2 class="font-display text-3xl tracking-tighter text-center text-primary mb-12">SEZONUN FAVORİLERİ</h2>
    <div class="max-w-[1400px] mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-10 md:gap-x-8 md:gap-y-16">
            <?php foreach ($itemsForGrid as $i => $item): ?>
                <?php
                if ($useRealProducts) {
                    $productUrl = $baseUrl . '/urun/' . htmlspecialchars($item['slug'] ?? '');
                    $itemName = $item['name'];
                    $itemPriceVal = (float) $item['price'];
                    $itemSalePrice = isset($item['sale_price']) && $item['sale_price'] !== null && $item['sale_price'] !== '' ? (float) $item['sale_price'] : null;
                    $itemHasSale = ($itemSalePrice !== null && $itemSalePrice > 0 && $itemSalePrice < $itemPriceVal);
                    $itemPrice = number_format($itemHasSale ? $itemSalePrice : $itemPriceVal, 2, ',', '.');
                    
                    // Gerçek ürün görsellerini kullan
                    $mainImgPath = isset($productImages[$item['id']]) ? $productImages[$item['id']] : null;
                    $img1 = null;
                    $img2 = null;
                    if ($mainImgPath) {
                        require_once __DIR__ . '/../app/Models/Product.php';
                        $allImages = \App\Models\Product::getImages($item['id']);
                        $img1 = $baseUrl . '/' . ($allImages[0] ?? $mainImgPath);
                        $img2 = isset($allImages[1]) ? ($baseUrl . '/' . $allImages[1]) : null;
                    }
                    
                    // Renk varyantlarını ve görsellerini hazırla
                    $colorVariants = $productColorVariants[$item['id']] ?? [];
                    $colorImagesData = $productColorImages[$item['id']] ?? [];
                    $colorImagesWithUrls = [];
                    $firstColorId = null;
                    foreach ($colorVariants as $color) {
                        $colorId = (int)$color['id'];
                        if ($firstColorId === null) {
                            $firstColorId = $colorId;
                        }
                        if (isset($colorImagesData[$colorId]) && !empty($colorImagesData[$colorId])) {
                            $colorImagesWithUrls[$colorId] = array_map(function($path) use ($baseUrl) {
                                return $baseUrl . '/' . $path;
                            }, $colorImagesData[$colorId]);
                        }
                    }
                    
                    // İlk renk için hover görseli (2. görsel varsa)
                    $hoverImg = null;
                    if ($firstColorId && isset($colorImagesWithUrls[$firstColorId]) && count($colorImagesWithUrls[$firstColorId]) > 1) {
                        $hoverImg = $colorImagesWithUrls[$firstColorId][1];
                    } elseif ($img2) {
                        $hoverImg = $img2;
                    }
                    
                    $cardData = [
                        'currentImg' => $img1,
                        'hoverImg' => $hoverImg,
                        'selectedColorId' => $firstColorId,
                        'colorImages' => $colorImagesWithUrls,
                        'hasColorImages' => !empty($colorImagesWithUrls),
                    ];
                } else {
                    $productUrl = $baseUrl . '/';
                    $itemName = $item['name'];
                    $itemPrice = $item['price'];
                    $img1 = getLuminaImage('product', $i % 6);
                    $img2 = getLuminaImage('hero', $i % 4);
                    $c0 = $item['colors'][0] ?? '#0a0a0a';
                    $c1 = $item['colors'][1] ?? '#c4a77d';
                    $c2 = $item['colors'][2] ?? '#ffffff';
                    $cardData = [
                        'currentImg' => $img1,
                        'selectedColor' => 'black',
                        'imgBlack' => $img1,
                        'imgBeige' => $img1,
                        'imgWhite' => $img1,
                    ];
                }
                ?>
                <div
                    x-show="<?= (int) $i ?> < limit"
                    x-transition:enter="transition ease-out duration-1000"
                    x-transition:enter-start="opacity-0 translate-y-12"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    <a href="<?= $productUrl ?>" class="cursor-pointer block" aria-label="<?= htmlspecialchars($itemName) ?>"
                        x-data="<?= htmlspecialchars(json_encode($cardData), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <div class="group relative overflow-hidden aspect-[3/4] bg-gray-100 mb-4"
                             :class="{ 'group-hover:scale-105': !hoverImg }"
                             style="transition: transform 0.5s ease;">
                            <?php if ($useRealProducts && isset($img1) && $img1): ?>
                                <img :src="currentImg" alt="<?= htmlspecialchars($itemName) ?>" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover:opacity-0" />
                                <template x-if="hoverImg">
                                    <img :src="hoverImg" alt="<?= htmlspecialchars($itemName) ?>" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                                </template>
                            <?php elseif (!$useRealProducts && isset($img1) && $img1): ?>
                                <img :src="currentImg" alt="<?= htmlspecialchars($itemName) ?>" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover:opacity-0" />
                                <?php if (isset($img2) && $img2 && $img2 !== $img1): ?>
                                    <img src="<?= htmlspecialchars($img2) ?>" alt="<?= htmlspecialchars($itemName) ?>" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-gray-400 mb-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h18.75A2.25 2.25 0 0 0 21 18.75V8.25A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21Z" />
                                        </svg>
                                        <p class="text-xs text-gray-500">Görsel yok</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($useRealProducts && (int)($item['is_new'] ?? 0) === 1): ?>
                                <span class="absolute top-2 left-2 text-[10px] bg-white px-2 py-1 tracking-widest uppercase text-primary">NEW</span>
                            <?php elseif (!$useRealProducts): ?>
                                <span class="absolute top-2 left-2 text-[10px] bg-white px-2 py-1 tracking-widest uppercase text-primary">NEW</span>
                            <?php endif; ?>
                            <?php if ($useRealProducts && (int)($item['is_featured'] ?? 0) === 1): ?>
                                <span class="absolute top-2 <?= (int)($item['is_new'] ?? 0) === 1 ? 'top-10' : 'top-2' ?> left-2 text-[10px] bg-primary text-white px-2 py-1 tracking-widest uppercase">Öne Çıkan</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm font-medium text-primary mt-3"><?= htmlspecialchars($itemName) ?></p>
                        <p class="text-xs text-secondary mt-1">
                            <?php if ($useRealProducts && isset($itemHasSale) && $itemHasSale): ?>
                                <span class="text-rose-600">₺<?= htmlspecialchars($itemPrice) ?></span>
                                <span class="ml-1 text-gray-400 line-through text-[10px]">₺<?= number_format($itemPriceVal, 2, ',', '.') ?></span>
                            <?php else: ?>
                                ₺<?= htmlspecialchars($itemPrice) ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($useRealProducts && isset($item['id']) && isset($productHasVariants[$item['id']]) && $productHasVariants[$item['id']] && !empty($colorVariants)): ?>
                            <div class="flex gap-1 mt-2" @click.prevent.stop>
                                <?php foreach ($colorVariants as $color): ?>
                                    <?php
                                    $colorId = (int)$color['id'];
                                    $colorName = htmlspecialchars($color['value']);
                                    $colorHex = $color['color_hex'] ?? '#cccccc';
                                    $hasColorImgs = isset($colorImagesWithUrls[$colorId]) && !empty($colorImagesWithUrls[$colorId]);
                                    // Renk bazlı görseller varsa onları kullan, yoksa ana ürün görsellerini kullan
                                    $colorFirstImg = $hasColorImgs ? $colorImagesWithUrls[$colorId][0] : ($img1 ?? '');
                                    $colorHoverImg = null;
                                    if ($hasColorImgs && count($colorImagesWithUrls[$colorId]) > 1) {
                                        $colorHoverImg = $colorImagesWithUrls[$colorId][1];
                                    } elseif (!$hasColorImgs && isset($img2) && $img2) {
                                        $colorHoverImg = $img2;
                                    }
                                    // Null kontrolü ile htmlspecialchars
                                    $colorFirstImgEscaped = $colorFirstImg ? htmlspecialchars($colorFirstImg, ENT_QUOTES) : '';
                                    $colorHoverImgEscaped = $colorHoverImg ? htmlspecialchars($colorHoverImg, ENT_QUOTES) : '';
                                    ?>
                                    <button type="button" 
                                            aria-label="<?= $colorName ?>"
                                            class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black transition"
                                            :class="{ 'ring-1 ring-black': selectedColorId === <?= $colorId ?> }"
                                            style="background-color: <?= htmlspecialchars($colorHex) ?>; filter: saturate(0.75) brightness(1.05) sepia(0.1);"
                                            @click.prevent="selectedColorId = <?= $colorId ?>; currentImg = '<?= $colorFirstImgEscaped ?>'; hoverImg = <?= $colorHoverImgEscaped ? "'" . $colorHoverImgEscaped . "'" : 'null' ?>;">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif (!$useRealProducts): ?>
                            <div class="flex gap-1 mt-2" @click.prevent.stop>
                                <button type="button" aria-label="Siyah renk"
                                    class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black"
                                    :class="{ 'ring-1 ring-black': selectedColor === 'black' }"
                                    style="background-color: <?= htmlspecialchars($c0) ?>"
                                    @click.prevent="currentImg = imgBlack; selectedColor = 'black'"
                                ></button>
                                <button type="button" aria-label="Bej renk"
                                    class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black"
                                    :class="{ 'ring-1 ring-black': selectedColor === 'beige' }"
                                    style="background-color: <?= htmlspecialchars($c1) ?>"
                                    @click.prevent="currentImg = imgBeige; selectedColor = 'beige'"
                                ></button>
                                <button type="button" aria-label="Beyaz renk"
                                    class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10 cursor-pointer focus:outline-none focus:ring-1 focus:ring-black"
                                    :class="{ 'ring-1 ring-black': selectedColor === 'white' }"
                                    style="background-color: <?= htmlspecialchars($c2) ?>"
                                    @click.prevent="currentImg = imgWhite; selectedColor = 'white'"
                                ></button>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Scroll tetikleyici: bu alan görünür olunca limit = total yapılır, kalan ürünler animasyonla açılır -->
        <div x-intersect.full="limit = total" class="h-10 w-full" aria-hidden="true"></div>
    </div>
</section>

<?php
use App\Models\Product;
$sortLabels = ['default' => 'Varsayılan', 'newest' => 'Yeniler', 'price_asc' => 'Fiyat (artan)', 'price_desc' => 'Fiyat (azalan)', 'name_asc' => 'İsim (A-Z)'];
$categorySlug = $category['slug'] ?? '';
$categoryName = $category['name'] ?? '';
$categoryDescription = !empty($category['description']) ? $category['description'] : 'Zamansız parçalar, modern kesimler ve sürdürülebilir kumaşlar.';
$totalRows = $totalRows ?? count($products ?? []);
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
$perPage = $perPage ?? 12;
$sort = $sort ?? 'default';
$currentSortLabel = $sortLabels[$sort] ?? $sortLabels['default'];
$productHasVariants = $productHasVariants ?? [];
$productColorImages = $productColorImages ?? [];
$productColorVariants = $productColorVariants ?? [];
?>
<!-- Kategori Başlığı -->
<header class="pt-32 pb-12 text-center">
    <h1 class="font-display text-4xl tracking-tighter text-primary mb-4"><?= htmlspecialchars(mb_strtoupper($categoryName)) ?></h1>
    <p class="text-gray-500 max-w-lg mx-auto text-sm"><?= nl2br(htmlspecialchars($categoryDescription)) ?></p>
</header>

<!-- Filtre & Araç Çubuğu -->
<div class="sticky top-[80px] z-40 bg-white/95 backdrop-blur border-y border-gray-100 py-4">
    <div class="max-w-[1400px] mx-auto px-6 flex flex-wrap justify-between items-center gap-4">
        <div class="flex gap-4">
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="flex items-center gap-2 text-xs uppercase tracking-widest text-primary hover:text-gray-500 transition">
                    Beden
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute top-full left-0 mt-2 w-48 bg-white border border-gray-100 shadow-lg py-2 z-50" @click.outside="open = false">
                    <p class="px-4 py-2 text-xs text-gray-400">Yakında</p>
                </div>
            </div>
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="flex items-center gap-2 text-xs uppercase tracking-widest text-primary hover:text-gray-500 transition">
                    Renk
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute top-full left-0 mt-2 w-48 bg-white border border-gray-100 shadow-lg py-2 z-50" @click.outside="open = false">
                    <p class="px-4 py-2 text-xs text-gray-400">Yakında</p>
                </div>
            </div>
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" class="flex items-center gap-2 text-xs uppercase tracking-widest text-primary hover:text-gray-500 transition">
                    Fiyat
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-cloak x-transition class="absolute top-full left-0 mt-2 w-48 bg-white border border-gray-100 shadow-lg py-2 z-50" @click.outside="open = false">
                    <p class="px-4 py-2 text-xs text-gray-400">Yakında</p>
                </div>
            </div>
        </div>
        <div class="flex items-center">
            <form method="get" action="<?= htmlspecialchars($baseUrl) ?>/kategori/<?= htmlspecialchars($categorySlug) ?>" class="flex items-center">
                <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">
                <label for="sort" class="text-xs uppercase tracking-widest text-primary mr-2">Sırala:</label>
                <select id="sort" name="sort" onchange="this.form.submit()" class="text-xs uppercase tracking-widest text-primary bg-transparent border-0 cursor-pointer focus:ring-0 focus:outline-none py-0 pr-6 appearance-none bg-no-repeat bg-right" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%236b7280%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22m19.5 8.25-7.5 7.5-7.5-7.5%22/%3E%3C/svg%3E'); background-size: 1rem;">
                    <?php foreach ($sortLabels as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $sort === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <span class="text-gray-400 text-xs ml-6"><?= (int) $totalRows ?> Ürün</span>
        </div>
    </div>
</div>

<!-- Ürün Listesi -->
<div class="max-w-[1400px] mx-auto px-6 py-10">
<?php if (empty($products)): ?>
    <p class="text-gray-500 text-center py-16">Bu kategoride henüz ürün yok.</p>
<?php else: ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-10 md:gap-x-8 md:gap-y-16">
        <?php foreach ($products as $i => $p): ?>
            <?php
            $price = (float) $p['price'];
            $salePrice = $p['sale_price'] !== null && $p['sale_price'] !== '' ? (float) $p['sale_price'] : null;
            $hasSale = ($salePrice !== null && $salePrice > 0 && $salePrice < $price);
            $displayPrice = $hasSale ? $salePrice : $price;
            $discountPercent = $hasSale ? (int) round((($price - $salePrice) / $price) * 100) : 0;
            
            // Görselleri al
            $mainImgPath = $productImages[$p['id']] ?? null;
            $img1 = null;
            $img2 = null;
            if ($mainImgPath) {
                $allImages = Product::getImages($p['id']);
                $img1 = $baseUrl . '/' . ($allImages[0] ?? $mainImgPath);
                $img2 = isset($allImages[1]) ? ($baseUrl . '/' . $allImages[1]) : null;
            }
            
            $hasVariants = isset($productHasVariants[$p['id']]) && $productHasVariants[$p['id']];
            
            // Renk varyantlarını ve görsellerini hazırla
            $colorVariants = $productColorVariants[$p['id']] ?? [];
            $colorImagesData = $productColorImages[$p['id']] ?? [];
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
            ?>
            <div>
                <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" class="cursor-pointer block" aria-label="<?= htmlspecialchars($p['name']) ?>"
                    x-data="<?= htmlspecialchars(json_encode($cardData), ENT_QUOTES, 'UTF-8') ?>"
                >
                    <div class="group relative overflow-hidden aspect-[3/4] bg-gray-100 mb-4"
                         :class="{ 'group-hover:scale-105': !hoverImg }"
                         style="transition: transform 0.5s ease;">
                        <?php if ($img1): ?>
                            <img :src="currentImg" alt="<?= htmlspecialchars($p['name']) ?>" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 group-hover:opacity-0" />
                            <template x-if="hoverImg">
                                <img :src="hoverImg" alt="<?= htmlspecialchars($p['name']) ?>" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                            </template>
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
                        <?php if ((int)($p['is_new'] ?? 0) === 1): ?>
                            <span class="absolute top-2 left-2 text-[10px] bg-white px-2 py-1 tracking-widest uppercase text-primary">NEW</span>
                        <?php endif; ?>
                        <?php if ($discountPercent > 0): ?>
                            <span class="absolute top-2 right-2 text-[10px] bg-red-900 text-white px-2 py-1 uppercase tracking-widest">%<?= $discountPercent ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm font-medium text-primary mt-3"><?= htmlspecialchars($p['name']) ?></p>
                    <p class="text-xs text-secondary mt-1">
                        <?php if ($hasSale): ?>
                            <span class="text-rose-600">₺<?= number_format($displayPrice, 2, ',', '.') ?></span>
                            <span class="ml-1 text-gray-400 line-through text-[10px]">₺<?= number_format($price, 2, ',', '.') ?></span>
                        <?php else: ?>
                            ₺<?= number_format($displayPrice, 2, ',', '.') ?>
                        <?php endif; ?>
                    </p>
                    <?php if ($hasVariants && !empty($colorVariants)): ?>
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
                                } elseif (!$hasColorImgs && $img2) {
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
                    <?php endif; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-16 flex flex-wrap justify-center gap-2" aria-label="Sayfa numaraları">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $url = $baseUrl . '/kategori/' . htmlspecialchars($categorySlug) . '?sort=' . urlencode($sort) . '&per_page=' . (int)$perPage . '&page=' . $i; ?>
                <?php if ($i === (int) $page): ?>
                    <span class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 bg-primary text-white text-xs font-medium uppercase tracking-widest rounded"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $url ?>" class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 border border-gray-200 text-primary text-xs font-medium uppercase tracking-widest rounded hover:border-primary transition"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
</div>

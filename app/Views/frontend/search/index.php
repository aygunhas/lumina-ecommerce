<?php
use App\Models\Product;
$baseUrl = $baseUrl ?? '';
$q = $q ?? '';
$products = $products ?? [];
$productImages = $productImages ?? [];
$productHasVariants = $productHasVariants ?? [];
$productColorImages = $productColorImages ?? [];
$productColorVariants = $productColorVariants ?? [];
$sortLabels = ['newest' => 'En yeni', 'price_asc' => 'Fiyat (artan)', 'price_desc' => 'Fiyat (azalan)', 'name_asc' => 'İsim (A-Z)'];
$sort = $sort ?? 'newest';
$perPage = $perPage ?? 12;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalRows = $totalRows ?? 0;
$queryParams = ['q' => $q, 'sort' => $sort, 'per_page' => $perPage];
?>
<div class="max-w-[1400px] mx-auto px-6 py-12">
    <nav class="text-sm text-gray-500 mb-6">
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="hover:text-primary transition">Anasayfa</a>
        <span class="text-gray-400"> / </span>
        <span class="text-primary">Arama: <?= htmlspecialchars($q) ?></span>
    </nav>

    <header class="mb-8">
        <h1 class="font-display text-3xl md:text-4xl tracking-tight text-primary mb-2">Arama: &quot;<?= htmlspecialchars($q) ?>&quot;</h1>
        <?php if (!empty($products)): ?>
            <p class="text-sm text-gray-500"><?= (int) $totalRows ?> ürün bulundu.</p>
        <?php endif; ?>
    </header>

<?php if (empty($products)): ?>
    <p class="text-gray-500 py-16 text-center">Aramanızla eşleşen ürün bulunamadı. Farklı anahtar kelimeler deneyin.</p>
<?php else: ?>
    <div class="flex flex-wrap items-center gap-4 mb-8">
        <form method="get" action="<?= htmlspecialchars($baseUrl) ?>/arama" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">
            <label for="sort" class="text-xs font-medium text-gray-600 uppercase tracking-wider">Sırala:</label>
            <select id="sort" name="sort" onchange="this.form.submit()" class="text-sm border border-gray-300 rounded-md py-2 px-3 focus:ring-2 focus:ring-black focus:border-black transition bg-white">
                <?php foreach ($sortLabels as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val) ?>" <?= $sort === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <form method="get" action="<?= htmlspecialchars($baseUrl) ?>/arama" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <label for="per_page" class="text-xs font-medium text-gray-600 uppercase tracking-wider">Sayfa başına:</label>
            <select id="per_page" name="per_page" onchange="this.form.submit()" class="text-sm border border-gray-300 rounded-md py-2 px-3 focus:ring-2 focus:ring-black focus:border-black transition bg-white">
                <?php foreach ([12, 24] as $n): ?>
                    <option value="<?= $n ?>" <?= (int)$perPage === $n ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

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
                    <?php if (!empty($p['short_description'])): ?>
                        <p class="text-xs text-gray-500 mb-1"><?= htmlspecialchars(mb_substr($p['short_description'], 0, 80)) ?><?= mb_strlen($p['short_description']) > 80 ? '…' : '' ?></p>
                    <?php endif; ?>
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
                <?php $url = $baseUrl . '/arama?' . http_build_query(array_merge($queryParams, ['page' => $i])); ?>
                <?php if ($i === (int) $page): ?>
                    <span class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 bg-primary text-white text-xs font-medium uppercase tracking-widest rounded"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($url) ?>" class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 border border-gray-200 text-primary text-xs font-medium uppercase tracking-widest rounded hover:border-primary transition"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
</div>

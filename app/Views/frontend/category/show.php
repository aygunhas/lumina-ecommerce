<?php
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
    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-10 md:gap-x-6 md:gap-y-12">
        <?php foreach ($products as $p): ?>
            <?php
            $price = (float) $p['price'];
            $salePrice = $p['sale_price'] !== null ? (float) $p['sale_price'] : null;
            $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price;
            $discountPercent = ($salePrice !== null && $salePrice > 0 && $price > $salePrice) ? (int) round((($price - $salePrice) / $price) * 100) : 0;
            ?>
            <?php $productImages = $productImages ?? []; $imgPath = $productImages[$p['id']] ?? null; ?>
            <li class="group">
                <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" class="block text-primary">
                    <div class="relative aspect-[3/4] bg-gray-100 rounded overflow-hidden mb-3">
                        <?php if ($imgPath): ?>
                            <img src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                        <?php else: ?>
                            <span class="absolute inset-0 flex items-center justify-center text-secondary text-sm">Görsel yok</span>
                        <?php endif; ?>
                        <?php if ((int)($p['is_featured'] ?? 0) === 1): ?>
                            <span class="absolute top-2 left-2 text-[10px] bg-primary text-white px-2 py-1 uppercase tracking-widest">Öne çıkan</span>
                        <?php endif; ?>
                        <?php if ((int)($p['is_new'] ?? 0) === 1): ?>
                            <span class="absolute top-2 <?= (int)($p['is_featured'] ?? 0) === 1 ? 'left-24' : 'left-2' ?> text-[10px] border border-primary text-primary bg-white px-2 py-1 uppercase tracking-widest">Yeni</span>
                        <?php endif; ?>
                        <?php if ($discountPercent > 0): ?>
                            <span class="absolute top-2 right-2 text-[10px] bg-red-900 text-white px-2 py-1 uppercase tracking-widest">%<?= $discountPercent ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-sm font-medium tracking-tight mb-1 group-hover:underline"><?= htmlspecialchars($p['name']) ?></h2>
                    <p class="text-sm text-secondary">
                        <?= number_format($displayPrice, 2, ',', '.') ?> ₺
                        <?php if ($salePrice !== null && $salePrice > 0): ?>
                            <span class="line-through text-gray-400"><?= number_format($price, 2, ',', '.') ?> ₺</span>
                        <?php endif; ?>
                    </p>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

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

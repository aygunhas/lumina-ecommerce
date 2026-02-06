<?php
$baseUrl = $baseUrl ?? '';
$q = $q ?? '';
$products = $products ?? [];
$productImages = $productImages ?? [];
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

    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-10 md:gap-x-6 md:gap-y-12">
        <?php foreach ($products as $p): ?>
            <?php
            $price = (float) $p['price'];
            $salePrice = $p['sale_price'] !== null ? (float) $p['sale_price'] : null;
            $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price;
            $discountPercent = ($salePrice !== null && $salePrice > 0 && $price > $salePrice) ? (int) round((($price - $salePrice) / $price) * 100) : 0;
            ?>
            <?php $imgPath = $productImages[$p['id']] ?? null; ?>
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
                    <?php if (!empty($p['short_description'])): ?>
                        <p class="text-xs text-gray-500 mb-1"><?= htmlspecialchars(mb_substr($p['short_description'], 0, 80)) ?><?= mb_strlen($p['short_description']) > 80 ? '…' : '' ?></p>
                    <?php endif; ?>
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

<?php
$sortLabels = ['newest' => 'En yeni', 'price_asc' => 'Fiyat (artan)', 'price_desc' => 'Fiyat (azalan)', 'name_asc' => 'İsim (A-Z)'];
$queryParams = ['q' => $q];
?>
<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <span>Arama: <?= htmlspecialchars($q) ?></span>
</nav>

<h1 style="margin: 0 0 1rem; font-size: 1.5rem;">Arama: &quot;<?= htmlspecialchars($q) ?>&quot;</h1>

<?php if (empty($products)): ?>
    <p style="color: #666;">Aramanızla eşleşen ürün bulunamadı. Farklı anahtar kelimeler deneyin.</p>
<?php else: ?>
    <p style="margin: 0 0 1rem; color: #666;"><?= (int) $totalRows ?> ürün bulundu.</p>

    <form method="get" action="<?= htmlspecialchars($baseUrl) ?>/arama" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
        <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
        <label for="sort" style="font-size: 0.9rem;">Sırala:</label>
        <select id="sort" name="sort" onchange="this.form.submit()" style="padding: 0.4rem 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <?php foreach ($sortLabels as $val => $label): ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= ($sort ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="per_page" style="font-size: 0.9rem;">Sayfa başına:</label>
        <select id="per_page" name="per_page" onchange="this.form.submit()" style="padding: 0.4rem 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <?php foreach ([12, 24] as $n): ?>
                <option value="<?= $n ?>" <?= ($perPage ?? 12) == $n ? 'selected' : '' ?>><?= $n ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <ul style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
        <?php foreach ($products as $p): ?>
            <?php
            $price = (float) $p['price'];
            $salePrice = $p['sale_price'] !== null ? (float) $p['sale_price'] : null;
            $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price;
            $discountPercent = ($salePrice !== null && $salePrice > 0 && $price > $salePrice) ? round((($price - $salePrice) / $price) * 100) : 0;
            ?>
            <?php $productImages = $productImages ?? []; $imgPath = $productImages[$p['id']] ?? null; ?>
            <li style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); position: relative;">
                <?php if ((int)($p['is_featured'] ?? 0) === 1): ?><span style="position: absolute; top: 8px; left: 8px; z-index: 1; font-size: 0.7rem; background: #3498db; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">Öne çıkan</span><?php endif; ?>
                <?php if ((int)($p['is_new'] ?? 0) === 1): ?><span style="position: absolute; top: 8px; <?= (int)($p['is_featured'] ?? 0) === 1 ? 'left: 80px;' : 'left: 8px;' ?> z-index: 1; font-size: 0.7rem; background: #27ae60; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">Yeni</span><?php endif; ?>
                <?php if ($discountPercent > 0): ?><span style="position: absolute; top: 8px; right: 8px; z-index: 1; font-size: 0.7rem; background: #e74c3c; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">%<?= $discountPercent ?> indirim</span><?php endif; ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" style="text-decoration: none; color: inherit; display: block;">
                    <div style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                        <?php if ($imgPath): ?>
                            <img src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-width: 100%; max-height: 200px; object-fit: contain;">
                        <?php else: ?>
                            <span style="color: #bbb; font-size: 0.85rem;">Görsel yok</span>
                        <?php endif; ?>
                    </div>
                    <span style="display: block; padding: 1rem;">
                        <strong style="display: block; margin-bottom: 0.5rem;"><?= htmlspecialchars($p['name']) ?></strong>
                        <?php if (!empty($p['short_description'])): ?>
                            <p style="font-size: 0.9rem; color: #666; margin: 0 0 0.5rem; line-height: 1.4;"><?= htmlspecialchars(mb_substr($p['short_description'], 0, 80)) ?><?= mb_strlen($p['short_description']) > 80 ? '…' : '' ?></p>
                        <?php endif; ?>
                        <p style="margin: 0; font-weight: 600;">
                            <?= number_format($displayPrice, 2, ',', '.') ?> ₺
                            <?php if ($salePrice !== null && $salePrice > 0): ?>
                                <span style="text-decoration: line-through; font-weight: normal; color: #999; font-size: 0.9rem;"><?= number_format($price, 2, ',', '.') ?> ₺</span>
                            <?php endif; ?>
                        </p>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($totalPages > 1): ?>
        <nav style="margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center;">
            <?php
            $baseQuery = http_build_query(array_merge($queryParams, ['sort' => $sort ?? 'newest', 'per_page' => $perPage ?? 12]));
            for ($i = 1; $i <= $totalPages; $i++):
                $url = $baseUrl . '/arama?' . $baseQuery . '&page=' . $i;
            ?>
                <?php if ($i === (int) $page): ?>
                    <span style="padding: 0.5rem 0.75rem; background: #333; color: #fff; border-radius: 4px;"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($url) ?>" style="padding: 0.5rem 0.75rem; background: #fff; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>

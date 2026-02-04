<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="color: #666;">Hesabım</a>
    <span style="color: #999;"> / </span>
    <span>Favorilerim</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Favorilerim</h1>

<?php if (empty($products)): ?>
    <p style="color: #666;">Favori listeniz boş. Ürün sayfalarındaki "Favorilere ekle" ile beğendiğiniz ürünleri ekleyebilirsiniz.</p>
    <p style="margin-top: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/">Alışverişe başla</a></p>
<?php else: ?>
    <ul style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
        <?php foreach ($products as $p): ?>
            <?php
            $price = (float) $p['price'];
            $salePrice = $p['sale_price'] !== null ? (float) $p['sale_price'] : null;
            $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price;
            $discountPercent = ($salePrice !== null && $salePrice > 0 && $price > $salePrice) ? round((($price - $salePrice) / $price) * 100) : 0;
            ?>
            <li style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); position: relative;">
                <?php if ((int)($p['is_featured'] ?? 0) === 1): ?><span style="position: absolute; top: 8px; left: 8px; z-index: 1; font-size: 0.7rem; background: #3498db; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">Öne çıkan</span><?php endif; ?>
                <?php if ($discountPercent > 0): ?><span style="position: absolute; top: 8px; right: 8px; z-index: 1; font-size: 0.7rem; background: #e74c3c; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">%<?= $discountPercent ?></span><?php endif; ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/urun/<?= htmlspecialchars($p['slug']) ?>" style="text-decoration: none; color: inherit; display: block;">
                    <?php $imgPath = $productImages[$p['id']] ?? null; ?>
                    <div style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                        <?php if ($imgPath): ?>
                            <img src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-width: 100%; max-height: 200px; object-fit: contain;">
                        <?php else: ?>
                            <span style="color: #bbb; font-size: 0.85rem;">Görsel yok</span>
                        <?php endif; ?>
                    </div>
                    <span style="display: block; padding: 1rem; padding-top: 0.75rem;">
                        <strong style="display: block; margin-bottom: 0.5rem;"><?= htmlspecialchars($p['name']) ?></strong>
                        <p style="margin: 0; font-weight: 600;">
                            <?= number_format($displayPrice, 2, ',', '.') ?> ₺
                            <?php if ($salePrice !== null && $salePrice > 0): ?>
                                <span style="text-decoration: line-through; font-weight: normal; color: #999; font-size: 0.9rem;"><?= number_format($price, 2, ',', '.') ?> ₺</span>
                            <?php endif; ?>
                        </p>
                    </span>
                </a>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/favori/sil" style="margin: 0 1rem 1rem; padding: 0;">
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl) ?>/hesabim/favoriler">
                    <button type="submit" style="width: 100%; padding: 0.4rem; font-size: 0.85rem; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; color: #666;">Favorilerden çıkar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <?php if (!empty($product['category_slug'])): ?>
        <span style="color: #999;"> / </span>
        <a href="<?= htmlspecialchars($baseUrl) ?>/kategori/<?= htmlspecialchars($product['category_slug']) ?>" style="color: #666;"><?= htmlspecialchars($product['category_name']) ?></a>
    <?php endif; ?>
    <span style="color: #999;"> / </span>
    <span><?= htmlspecialchars($product['name']) ?></span>
</nav>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
    <div style="background: #f5f5f5; border-radius: 8px; min-height: 300px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
        <?php if (!empty($productImagePath)): ?>
            <img src="<?= htmlspecialchars($baseUrl ?? '') ?>/<?= htmlspecialchars($productImagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width: 100%; max-height: 400px; object-fit: contain;">
        <?php else: ?>
            <span style="color: #999;">Ürün görseli yok</span>
        <?php endif; ?>
    </div>
    <div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
            <?php if ((int)($product['is_featured'] ?? 0) === 1): ?><span style="font-size: 0.75rem; background: #3498db; color: #fff; padding: 0.25rem 0.5rem; border-radius: 4px;">Öne çıkan</span><?php endif; ?>
            <?php if ((int)($product['is_new'] ?? 0) === 1): ?><span style="font-size: 0.75rem; background: #27ae60; color: #fff; padding: 0.25rem 0.5rem; border-radius: 4px;">Yeni</span><?php endif; ?>
            <?php
            $price = (float) $product['price'];
            $salePrice = $product['sale_price'] !== null ? (float) $product['sale_price'] : null;
            if ($salePrice !== null && $salePrice > 0 && $price > 0):
                $discountPercent = (int) round((($price - $salePrice) / $price) * 100);
                if ($discountPercent > 0):
            ?><span style="font-size: 0.75rem; background: #e74c3c; color: #fff; padding: 0.25rem 0.5rem; border-radius: 4px;">%<?= $discountPercent ?> İndirim</span><?php
                endif;
            endif;
            ?>
        </div>
        <h1 style="margin: 0 0 0.5rem; font-size: 1.5rem;"><?= htmlspecialchars($product['name']) ?></h1>
        <?php if (!empty($product['category_name'])): ?>
            <p style="margin: 0 0 1rem; font-size: 0.9rem; color: #666;">Kategori: <a href="<?= htmlspecialchars($baseUrl) ?>/kategori/<?= htmlspecialchars($product['category_slug']) ?>" style="color: #3498db;"><?= htmlspecialchars($product['category_name']) ?></a></p>
        <?php endif; ?>
        <?php $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price; ?>
        <p style="margin: 0 0 1rem; font-size: 1.25rem; font-weight: 600;">
            <?= number_format($displayPrice, 2, ',', '.') ?> ₺
            <?php if ($salePrice !== null && $salePrice > 0): ?>
                <span style="text-decoration: line-through; font-weight: normal; color: #999; font-size: 1rem;"><?= number_format($price, 2, ',', '.') ?> ₺</span>
            <?php endif; ?>
        </p>
        <?php if (!empty($product['short_description'])): ?>
            <p style="color: #555; margin-bottom: 1rem;"><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($product['description'])): ?>
            <div style="margin-bottom: 1rem;"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
        <?php endif; ?>
        <?php
        $hasVariants = !empty($productVariants);
        $productVariants = $productVariants ?? [];
        $attributesForVariant = $attributesForVariant ?? [];
        $attributeValuesByAttr = $attributeValuesByAttr ?? [];
        ?>
        <?php if ($hasVariants): ?>
            <div id="variant-selectors" style="margin-bottom: 1rem;">
                <?php foreach ($attributesForVariant as $attr): ?>
                    <?php $vals = $attributeValuesByAttr[$attr['id']] ?? []; if (empty($vals)) continue; ?>
                    <p style="margin: 0 0 0.5rem;"><strong><?= htmlspecialchars($attr['name']) ?></strong></p>
                    <select data-attribute-id="<?= (int) $attr['id'] ?>" class="variant-attr-select" style="min-width: 140px; padding: 0.5rem; margin-right: 0.5rem; margin-bottom: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">— Seçin —</option>
                        <?php foreach ($vals as $av): ?>
                            <option value="<?= (int) $av['id'] ?>"><?= htmlspecialchars($av['value']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>
                <p id="variant-info" style="margin: 0.75rem 0 0; font-size: 0.9rem; color: #666;"></p>
            </div>
            <form id="add-cart-variant-form" method="post" action="<?= htmlspecialchars($baseUrl) ?>/sepet/ekle" style="margin: 0; display: none;">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <input type="hidden" name="product_variant_id" id="product_variant_id" value="">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl) ?>/sepet">
                <label for="qty">Adet</label>
                <input type="number" id="qty" name="quantity" value="1" min="1" style="width: 70px; padding: 0.4rem; margin: 0 0.5rem;">
                <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Sepete ekle</button>
            </form>
            <p id="variant-please-select" style="margin: 0; color: #999; font-size: 0.9rem;">Lütfen beden ve renk seçin.</p>
            <script>
            (function(){
                var variants = <?= json_encode(array_map(function($v){ return ['id' => (int)$v['id'], 'attribute_value_ids' => array_map('intval', $v['attribute_value_ids'] ?? []), 'stock' => (int)$v['stock'], 'price' => $v['price'], 'sale_price' => $v['sale_price'], 'attributes_summary' => $v['attributes_summary']]; }, $productVariants)) ?>;
                var selects = document.querySelectorAll('.variant-attr-select');
                var form = document.getElementById('add-cart-variant-form');
                var variantInput = document.getElementById('product_variant_id');
                var variantInfo = document.getElementById('variant-info');
                var variantPlease = document.getElementById('variant-please-select');
                var qtyInput = document.getElementById('qty');
                function getSelectedIds(){
                    var ids = [];
                    selects.forEach(function(s){ var v = s.value; if(v) ids.push(parseInt(v,10)); });
                    return ids.sort(function(a,b){ return a-b; });
                }
                function findVariant(){
                    var selected = getSelectedIds();
                    if(selected.length !== selects.length) return null;
                    for(var i=0;i<variants.length;i++){
                        var vids = variants[i].attribute_value_ids.slice().sort(function(a,b){ return a-b; });
                        if(vids.length === selected.length && vids.every(function(v,j){ return v===selected[j]; }))
                            return variants[i];
                    }
                    return null;
                }
                function updateUI(){
                    var v = findVariant();
                    variantPlease.style.display = v ? 'none' : 'block';
                    form.style.display = v ? 'inline-block' : 'none';
                    if(!v){ variantInfo.textContent = ''; return; }
                    var price = v.sale_price && parseFloat(v.sale_price) > 0 ? parseFloat(v.sale_price) : parseFloat(v.price);
                    variantInfo.textContent = v.attributes_summary + ' — ' + (v.stock > 0 ? v.stock + ' adet, ' + price.toFixed(2).replace('.',',') + ' ₺' : 'Stok yok');
                    variantInput.value = v.id;
                    qtyInput.max = v.stock > 0 ? v.stock : 1;
                    qtyInput.value = v.stock > 0 ? Math.min(parseInt(qtyInput.value,10) || 1, v.stock) : 0;
                }
                selects.forEach(function(s){ s.addEventListener('change', updateUI); });
                updateUI();
            })();
            </script>
        <?php else: ?>
            <p style="margin-bottom: 1rem; font-size: 0.9rem; color: #666;">Stok: <?= (int) $product['stock'] > 0 ? (int) $product['stock'] . ' adet' : 'Stok yok' ?></p>
            <?php if ((int) $product['stock'] > 0): ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/sepet/ekle" style="margin: 0; display: inline-block;">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl) ?>/sepet">
                    <label for="qty">Adet</label>
                    <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>" style="width: 70px; padding: 0.4rem; margin: 0 0.5rem;">
                    <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Sepete ekle</button>
                </form>
            <?php else: ?>
                <p style="margin: 0; color: #999;">Stok yok — şu an sepete eklenemez.</p>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($userId)): ?>
            <?php if ($isInWishlist): ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/favori/sil" style="display: inline-block; margin-left: 0.75rem;">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl ?? '') ?>/urun/<?= htmlspecialchars($product['slug']) ?>">
                    <button type="submit" style="padding: 0.5rem 1rem; background: #f5f5f5; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; color: #c62828;">❤️ Favorilerden çıkar</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/favori/ekle" style="display: inline-block; margin-left: 0.75rem;">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($baseUrl ?? '') ?>/urun/<?= htmlspecialchars($product['slug']) ?>">
                    <button type="submit" style="padding: 0.5rem 1rem; background: #f5f5f5; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">♡ Favorilere ekle</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
    <section style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #eee;">
        <h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Bunları da beğenebilirsiniz</h2>
        <ul style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
            <?php foreach ($relatedProducts as $p): ?>
                <?php
                $price = (float) $p['price'];
                $salePrice = $p['sale_price'] !== null ? (float) $p['sale_price'] : null;
                $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $price;
                $discountPercent = ($salePrice !== null && $salePrice > 0 && $price > $salePrice) ? round((($price - $salePrice) / $price) * 100) : 0;
                ?>
                <li style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); position: relative;">
                    <?php if ((int)($p['is_featured'] ?? 0) === 1): ?><span style="position: absolute; top: 8px; left: 8px; z-index: 1; font-size: 0.7rem; background: #3498db; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">Öne çıkan</span><?php endif; ?>
                    <?php if ((int)($p['is_new'] ?? 0) === 1): ?><span style="position: absolute; top: 8px; <?= (int)($p['is_featured'] ?? 0) === 1 ? 'left: 80px;' : 'left: 8px;' ?> z-index: 1; font-size: 0.7rem; background: #27ae60; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">Yeni</span><?php endif; ?>
                    <?php if ($discountPercent > 0): ?><span style="position: absolute; top: 8px; right: 8px; z-index: 1; font-size: 0.7rem; background: #e74c3c; color: #fff; padding: 0.2rem 0.4rem; border-radius: 4px;">%<?= $discountPercent ?></span><?php endif; ?>
                    <a href="<?= htmlspecialchars($baseUrl ?? '') ?>/urun/<?= htmlspecialchars($p['slug']) ?>" style="text-decoration: none; color: inherit; display: block;">
                        <?php $imgPath = $relatedProductImages[$p['id']] ?? null; ?>
                        <div style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                            <?php if ($imgPath): ?>
                                <img src="<?= htmlspecialchars($baseUrl ?? '') ?>/<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-width: 100%; max-height: 200px; object-fit: contain;">
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
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

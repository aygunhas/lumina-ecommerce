<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Ürün düzenle</h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php if (!empty($_GET['variant_removed'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px; font-size: 0.9rem;">Varyant kaldırıldı.</p>
<?php endif; ?>

<?php
$old = $old ?? [];
$name = $old['name'] ?? $product['name'];
$categoryId = $old['category_id'] ?? $product['category_id'];
$description = $old['description'] ?? $product['description'];
$shortDescription = $old['short_description'] ?? $product['short_description'];
$price = $old['price'] ?? $product['price'];
$salePrice = $old['sale_price'] ?? $product['sale_price'];
$sku = $old['sku'] ?? $product['sku'];
$stock = $old['stock'] ?? $product['stock'];
$lowStockThreshold = $old['low_stock_threshold'] ?? $product['low_stock_threshold'];
$sortOrder = $old['sort_order'] ?? $product['sort_order'];
$isFeatured = isset($old['is_featured']) ? (int) $old['is_featured'] : (int) $product['is_featured'];
$isNew = isset($old['is_new']) ? (int) $old['is_new'] : (int) $product['is_new'];
$isActive = isset($old['is_active']) ? (int) $old['is_active'] : (int) $product['is_active'];
?>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) $product['id'] ?>" enctype="multipart/form-data" style="max-width: 600px;">
    <p style="margin-bottom: 0.5rem;"><label for="name">Ürün adı <span style="color: #c00;">*</span></label></p>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="category_id">Kategori</label></p>
    <select id="category_id" name="category_id" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <option value="">— Seçin —</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (string) $categoryId === (string) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <p style="margin-bottom: 0.5rem;"><label for="description">Açıklama</label></p>
    <textarea id="description" name="description" rows="4" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($description ?? '') ?></textarea>

    <p style="margin-bottom: 0.5rem;"><label for="short_description">Kısa açıklama</label></p>
    <input type="text" id="short_description" name="short_description" value="<?= htmlspecialchars($shortDescription ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="price">Fiyat (₺) <span style="color: #c00;">*</span></label></p>
    <input type="text" id="price" name="price" value="<?= htmlspecialchars((string) $price) ?>" required style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="sale_price">İndirimli fiyat (₺)</label></p>
    <input type="text" id="sale_price" name="sale_price" value="<?= $salePrice !== null && $salePrice !== '' ? htmlspecialchars((string) $salePrice) : '' ?>" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="sku">SKU</label></p>
    <input type="text" id="sku" name="sku" value="<?= htmlspecialchars($sku ?? '') ?>" style="width: 150px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="stock">Stok</label></p>
    <input type="number" id="stock" name="stock" value="<?= (int) $stock ?>" min="0" style="width: 100px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="low_stock_threshold">Düşük stok uyarı eşiği</label></p>
    <input type="number" id="low_stock_threshold" name="low_stock_threshold" value="<?= (int) $lowStockThreshold ?>" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="sort_order">Sıra</label></p>
    <input type="number" id="sort_order" name="sort_order" value="<?= (int) $sortOrder ?>" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <?php $productImages = $productImages ?? []; ?>
    <?php if (!empty($_GET['image_removed'])): ?>
        <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px; font-size: 0.9rem;">Görsel kaldırıldı.</p>
    <?php endif; ?>
    <?php if (!empty($productImages)): ?>
        <p style="margin-bottom: 0.5rem;">Mevcut görsel(ler)</p>
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
            <?php foreach ($productImages as $img): ?>
                <div style="position: relative; display: inline-block;">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($img['path']) ?>" alt="" style="max-width: 120px; max-height: 120px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px;">
                    <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/products/delete-image" style="margin-top: 0.25rem;" onsubmit="return confirm('Bu görseli kaldırmak istediğinize emin misiniz?');">
                        <input type="hidden" name="image_id" value="<?= (int) $img['id'] ?>">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <button type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #c62828; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Kaldır</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <p style="margin-bottom: 0.5rem;"><label for="image">Yeni görsel ekle (isteğe bağlı, JPG/PNG/WebP, max 2 MB)</label></p>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" style="margin-bottom: 1rem;">

    <p style="margin-bottom: 0.5rem;">
        <label><input type="checkbox" name="is_featured" value="1" <?= $isFeatured ? 'checked' : '' ?>> Öne çıkan</label><br>
        <label><input type="checkbox" name="is_new" value="1" <?= $isNew ? 'checked' : '' ?>> Yeni</label><br>
        <label><input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>> Aktif</label>
    </p>

    <p style="margin-top: 1.5rem;">
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Güncelle</button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products" style="margin-left: 0.5rem; color: #666;">İptal</a>
    </p>
</form>

<?php $productVariants = $productVariants ?? []; $attributesForVariant = $attributesForVariant ?? []; $attributeValuesByAttr = $attributeValuesByAttr ?? []; ?>
<section id="variants" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #ddd;">
    <h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Varyantlar (Beden / Renk)</h2>
    <?php if (!empty($errors['variant'])): ?>
        <p style="margin-bottom: 0.75rem; color: #c00;"><?= htmlspecialchars($errors['variant']) ?></p>
    <?php endif; ?>
    <?php if (!empty($productVariants)): ?>
        <table style="width: 100%; max-width: 700px; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr style="border-bottom: 1px solid #ccc; text-align: left;">
                    <th style="padding: 0.5rem;">SKU</th>
                    <th style="padding: 0.5rem;">Özellikler</th>
                    <th style="padding: 0.5rem;">Stok</th>
                    <th style="padding: 0.5rem;">Fiyat</th>
                    <th style="padding: 0.5rem;">İndirimli</th>
                    <th style="padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productVariants as $v): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.5rem;"><?= htmlspecialchars($v['sku']) ?></td>
                        <td style="padding: 0.5rem;"><?= htmlspecialchars($v['attributes_summary'] ?? '') ?></td>
                        <td style="padding: 0.5rem;"><?= (int) $v['stock'] ?></td>
                        <td style="padding: 0.5rem;"><?= $v['price'] !== null ? htmlspecialchars((string) $v['price']) . ' ₺' : '—' ?></td>
                        <td style="padding: 0.5rem;"><?= $v['sale_price'] !== null && $v['sale_price'] !== '' ? htmlspecialchars((string) $v['sale_price']) . ' ₺' : '—' ?></td>
                        <td style="padding: 0.5rem;">
                            <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/products/delete-variant" style="display: inline;" onsubmit="return confirm('Bu varyantı kaldırmak istediğinize emin misiniz?');">
                                <input type="hidden" name="variant_id" value="<?= (int) $v['id'] ?>">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <button type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #c62828; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="margin-bottom: 1rem; color: #666;">Henüz varyant yok. Aşağıdan ekleyebilirsiniz.</p>
    <?php endif; ?>

    <h3 style="margin: 0 0 0.75rem; font-size: 1rem;">Yeni varyant ekle</h3>
    <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/products/add-variant" style="max-width: 600px;">
        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <?php foreach ($attributesForVariant as $attr): ?>
            <?php $vals = $attributeValuesByAttr[$attr['id']] ?? []; if (empty($vals)) continue; ?>
            <p style="margin-bottom: 0.5rem;"><label for="attr_<?= (int) $attr['id'] ?>"><?= htmlspecialchars($attr['name']) ?></label></p>
            <select id="attr_<?= (int) $attr['id'] ?>" name="attribute_value_id[]" style="width: 100%; max-width: 200px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">— Seçin —</option>
                <?php foreach ($vals as $av): ?>
                    <option value="<?= (int) $av['id'] ?>"><?= htmlspecialchars($av['value']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endforeach; ?>
        <p style="margin-bottom: 0.5rem;"><label for="variant_sku">SKU <span style="color: #c00;">*</span></label></p>
        <input type="text" id="variant_sku" name="variant_sku" required placeholder="örn. ELBISE-M-KIRMIZI" style="width: 100%; max-width: 220px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="variant_stock">Stok</label></p>
        <input type="number" id="variant_stock" name="variant_stock" value="0" min="0" style="width: 100px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="variant_price">Fiyat (₺) — boş bırakılırsa ana ürün fiyatı kullanılır</label></p>
        <input type="text" id="variant_price" name="variant_price" placeholder="opsiyonel" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p style="margin-bottom: 0.5rem;"><label for="variant_sale_price">İndirimli fiyat (₺)</label></p>
        <input type="text" id="variant_sale_price" name="variant_sale_price" placeholder="opsiyonel" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <p>
            <button type="submit" style="padding: 0.5rem 1rem; background: #2e7d32; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Varyant ekle</button>
        </p>
    </form>
</section>

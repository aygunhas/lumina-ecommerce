<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Yeni ürün</h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php $old = $old ?? []; ?>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/products/create" enctype="multipart/form-data" style="max-width: 600px;">
    <p style="margin-bottom: 0.5rem;"><label for="name">Ürün adı <span style="color: #c00;">*</span></label></p>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="category_id">Kategori</label></p>
    <select id="category_id" name="category_id" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">
        <option value="">— Seçin —</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= isset($old['category_id']) && (string) $old['category_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <p style="margin-bottom: 0.5rem;"><label for="description">Açıklama</label></p>
    <textarea id="description" name="description" rows="4" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>

    <p style="margin-bottom: 0.5rem;"><label for="short_description">Kısa açıklama</label></p>
    <input type="text" id="short_description" name="short_description" value="<?= htmlspecialchars($old['short_description'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="price">Fiyat (₺) <span style="color: #c00;">*</span></label></p>
    <input type="text" id="price" name="price" value="<?= htmlspecialchars($old['price'] ?? '0') ?>" required style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="sale_price">İndirimli fiyat (₺) — boş bırakırsanız indirim yok</label></p>
    <input type="text" id="sale_price" name="sale_price" value="<?= htmlspecialchars($old['sale_price'] ?? '') ?>" style="width: 120px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="sku">SKU</label></p>
    <input type="text" id="sku" name="sku" value="<?= htmlspecialchars($old['sku'] ?? '') ?>" style="width: 150px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="stock">Stok</label></p>
    <input type="number" id="stock" name="stock" value="<?= (int) ($old['stock'] ?? 0) ?>" min="0" style="width: 100px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="low_stock_threshold">Düşük stok uyarı eşiği</label></p>
    <input type="number" id="low_stock_threshold" name="low_stock_threshold" value="<?= (int) ($old['low_stock_threshold'] ?? 5) ?>" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="sort_order">Sıra</label></p>
    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($old['sort_order'] ?? 0) ?>" min="0" style="width: 80px; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="image">Ürün görseli (isteğe bağlı, JPG/PNG/WebP, max 2 MB)</label></p>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" style="margin-bottom: 1rem;">

    <p style="margin-bottom: 0.5rem;">
        <label><input type="checkbox" name="is_featured" value="1" <?= !empty($old['is_featured']) ? 'checked' : '' ?>> Öne çıkan</label><br>
        <label><input type="checkbox" name="is_new" value="1" <?= !empty($old['is_new']) ? 'checked' : '' ?>> Yeni</label><br>
        <label><input type="checkbox" name="is_active" value="1" <?= !isset($old['is_active']) || !empty($old['is_active']) ? 'checked' : '' ?>> Aktif</label>
    </p>

    <p style="margin-top: 1.5rem;">
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Kaydet</button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products" style="margin-left: 0.5rem; color: #666;">İptal</a>
    </p>
</form>

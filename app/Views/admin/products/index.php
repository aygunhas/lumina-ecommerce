<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
    <h1 style="margin: 0; font-size: 1.5rem;">Ürünler</h1>
    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/create" style="display: inline-block; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Yeni ürün</a>
</div>

<form method="get" action="<?= htmlspecialchars($baseUrl) ?>/admin/products" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem; margin-bottom: 1.5rem; padding: 1rem; background: #f9f9f9; border-radius: 8px;">
    <div>
        <label for="q" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Ara (ürün adı, SKU)</label>
        <input type="text" id="q" name="q" value="<?= htmlspecialchars($filterQ ?? '') ?>" placeholder="Ara..." style="padding: 0.5rem; width: 200px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div>
        <label for="category_id" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Kategori</label>
        <select id="category_id" name="category_id" style="padding: 0.5rem; width: 160px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">— Tümü —</option>
            <?php foreach (($categories ?? []) as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (string)($filterCategoryId ?? '') === (string)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="stock" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Stok durumu</label>
        <select id="stock" name="stock" style="padding: 0.5rem; width: 140px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">— Tümü —</option>
            <option value="in_stock" <?= ($filterStock ?? '') === 'in_stock' ? 'selected' : '' ?>>Stokta</option>
            <option value="low_stock" <?= ($filterStock ?? '') === 'low_stock' ? 'selected' : '' ?>>Düşük stok</option>
            <option value="out_of_stock" <?= ($filterStock ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Stok yok</option>
        </select>
    </div>
    <div>
        <button type="submit" style="padding: 0.5rem 1rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Filtrele</button>
        <?php if (($filterQ ?? '') !== '' || ($filterCategoryId ?? '') !== '' || ($filterStock ?? '') !== ''): ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products" style="margin-left: 0.5rem; font-size: 0.9rem; color: #666;">Temizle</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($products)): ?>
    <p style="color: #666;">Henüz ürün yok. "Yeni ürün" ile ekleyebilirsiniz.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 700px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ürün</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Kategori</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">SKU</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Fiyat</th>
                    <th style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Stok</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <?php
                    $displayPrice = $p['sale_price'] !== null && (float) $p['sale_price'] > 0
                        ? number_format((float) $p['sale_price'], 2, ',', '.') . ' ₺'
                        : number_format((float) $p['price'], 2, ',', '.') . ' ₺';
                    if ($p['sale_price'] !== null && (float) $p['sale_price'] > 0) {
                        $displayPrice .= ' <span style="text-decoration: line-through; color: #999; font-size: 0.9rem;">' . number_format((float) $p['price'], 2, ',', '.') . ' ₺</span>';
                    }
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($p['name']) ?>
                            <?php if ((int) $p['is_featured']): ?><span style="font-size: 0.75rem; background: #3498db; color: #fff; padding: 0.1rem 0.35rem; border-radius: 3px; margin-left: 0.25rem;">Öne çıkan</span><?php endif; ?>
                            <?php if ((int) $p['is_new']): ?><span style="font-size: 0.75rem; background: #27ae60; color: #fff; padding: 0.1rem 0.35rem; border-radius: 3px; margin-left: 0.25rem;">Yeni</span><?php endif; ?>
                        </td>
                        <td style="padding: 0.75rem 1rem;"><?= $p['category_name'] ? htmlspecialchars($p['category_name']) : '—' ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= $p['sku'] ? htmlspecialchars($p['sku']) : '—' ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= $displayPrice ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: center;"><?= (int) $p['stock'] ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= (int) $p['is_active'] ? 'Aktif' : 'Pasif' ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) $p['id'] ?>" style="color: #3498db; text-decoration: none;">Düzenle</a>
                            <span style="color: #ccc;">|</span>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/delete?id=<?= (int) $p['id'] ?>" style="color: #c0392b; text-decoration: none;">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<p style="margin-bottom: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/reports">← Raporlara dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Stok raporu</h1>

<?php if (!empty($lowStock)): ?>
    <div style="margin-bottom: 2rem; padding: 1rem; background: #fff8e6; border: 1px solid #f0c000; border-radius: 8px;">
        <h2 style="margin: 0 0 0.75rem; font-size: 1.1rem; color: #b45309;">⚠️ Düşük stok uyarısı (<?= count($lowStock) ?> ürün)</h2>
        <ul style="margin: 0; padding-left: 1.25rem;">
            <?php foreach ($lowStock as $p): ?>
                <li style="margin-bottom: 0.25rem;">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                    — Stok: <strong><?= (int) $p['stock'] ?></strong>, Eşik: <?= (int) ($p['low_stock_threshold'] ?? 5) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Tüm ürünler (stok sırasına göre)</h2>
<?php if (empty($products)): ?>
    <p style="color: #666;">Ürün yok.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 550px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ürün</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Kategori</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">SKU</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Stok</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Eşik</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <?php
                    $isLow = (int) $p['stock'] <= (int) ($p['low_stock_threshold'] ?? 5);
                    if ((int) ($p['low_stock_threshold'] ?? 0) === 0) {
                        $isLow = (int) $p['stock'] <= 5;
                    }
                    ?>
                    <tr style="border-bottom: 1px solid #eee; <?= $isLow ? 'background: #fff8e6;' : '' ?>">
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($p['sku'] ?? '—') ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right; <?= $isLow ? 'font-weight: bold; color: #c62828;' : '' ?>"><?= (int) $p['stock'] ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= (int) ($p['low_stock_threshold'] ?? 5) ?></td>
                        <td style="padding: 0.75rem 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) $p['id'] ?>">Düzenle</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

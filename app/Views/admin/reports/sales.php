<p style="margin-bottom: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/reports">← Raporlara dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Satış raporu</h1>

<form method="get" action="<?= htmlspecialchars($baseUrl) ?>/admin/reports/sales" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem; margin-bottom: 1.5rem; padding: 1rem; background: #f9f9f9; border-radius: 8px;">
    <div>
        <label for="date_from" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Başlangıç tarihi</label>
        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div>
        <label for="date_to" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Bitiş tarihi</label>
        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div>
        <button type="submit" style="padding: 0.5rem 1rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Göster</button>
    </div>
</form>

<?php $s = $summary ?? []; ?>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div style="background: #fff; padding: 1.25rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <strong style="display: block; font-size: 1.5rem; color: #333;"><?= (int) ($s['order_count'] ?? 0) ?></strong>
        <span style="font-size: 0.9rem; color: #666;">Sipariş sayısı</span>
    </div>
    <div style="background: #fff; padding: 1.25rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <strong style="display: block; font-size: 1.5rem; color: #333;"><?= number_format((float) ($s['total_sales'] ?? 0), 2, ',', '.') ?> ₺</strong>
        <span style="font-size: 0.9rem; color: #666;">Toplam satış</span>
    </div>
</div>

<h2 style="margin: 0 0 1rem; font-size: 1.2rem;">En çok satan ürünler (top 20)</h2>
<?php if (empty($topProducts)): ?>
    <p style="color: #666;">Seçilen tarih aralığında satış yok.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 500px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ürün</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">SKU</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Adet</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Tutar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProducts as $p): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($p['product_name']) ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($p['product_sku'] ?? '—') ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= (int) $p['total_qty'] ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format((float) $p['total_amount'], 2, ',', '.') ?> ₺</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

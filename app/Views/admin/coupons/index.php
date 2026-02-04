<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
    <h1 style="margin: 0; font-size: 1.5rem;">Kuponlar</h1>
    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/coupons/create" style="display: inline-block; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Yeni kupon</a>
</div>

<?php if (!empty($_GET['created'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Kupon oluşturuldu.</p>
<?php endif; ?>
<?php if (!empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Kupon güncellendi.</p>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Kupon silindi.</p>
<?php endif; ?>

<?php if (empty($coupons)): ?>
    <p style="color: #666;">Henüz kupon yok. "Yeni kupon" ile ekleyebilirsiniz.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 700px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Kod</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Tip</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Değer</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Min. sepet</th>
                    <th style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Kullanım</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Geçerlilik</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $c): ?>
                    <?php
                    $typeLabel = $c['type'] === 'percent' ? 'Yüzde' : 'Sabit tutar';
                    $valueStr = $c['type'] === 'percent' ? (float)$c['value'] . '%' : number_format((float)$c['value'], 2, ',', '.') . ' ₺';
                    $used = (int) $c['used_count'];
                    $max = $c['max_use_count'] !== null ? (int) $c['max_use_count'] : null;
                    $remaining = $max === null ? '∞' : max(0, $max - $used);
                    $starts = $c['starts_at'] ? date('d.m.Y', strtotime($c['starts_at'])) : '—';
                    $ends = $c['ends_at'] ? date('d.m.Y', strtotime($c['ends_at'])) : '—';
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><strong><?= htmlspecialchars($c['code']) ?></strong></td>
                        <td style="padding: 0.75rem 1rem;"><?= $typeLabel ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= $valueStr ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= $c['min_order_amount'] !== null ? number_format((float)$c['min_order_amount'], 2, ',', '.') . ' ₺' : '—' ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: center;"><?= $used ?> / <?= $max !== null ? $max : '∞' ?> (kalan: <?= $remaining ?>)</td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.9rem;"><?= $starts ?> – <?= $ends ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= (int)$c['is_active'] === 1 ? 'Aktif' : 'Pasif' ?></td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/coupons/edit?id=<?= (int) $c['id'] ?>">Düzenle</a>
                            <span style="color: #ccc;">|</span>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/coupons/delete?id=<?= (int) $c['id'] ?>" style="color: #c0392b;">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Müşteriler</h1>

<form method="get" action="<?= htmlspecialchars($baseUrl) ?>/admin/customers" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem; margin-bottom: 1.5rem; padding: 1rem; background: #f9f9f9; border-radius: 8px;">
    <div>
        <label for="q" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Ara (ad, e-posta, telefon)</label>
        <input type="text" id="q" name="q" value="<?= htmlspecialchars($filterQ ?? '') ?>" placeholder="Ara..." style="padding: 0.5rem; width: 260px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div>
        <button type="submit" style="padding: 0.5rem 1rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Ara</button>
        <?php if (($filterQ ?? '') !== ''): ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/customers" style="margin-left: 0.5rem; font-size: 0.9rem; color: #666;">Temizle</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($customers)): ?>
    <p style="color: #666;">Kayıtlı müşteri bulunamadı. Mağazadan kayıt olan üyeler burada listelenir.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 600px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ad Soyad</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">E-posta</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Telefon</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Son sipariş</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Kayıt</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) ?></td>
                        <td style="padding: 0.75rem 1rem;"><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a></td>
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.9rem;"><?= !empty($c['last_order_at']) ? date('d.m.Y H:i', strtotime($c['last_order_at'])) : '—' ?></td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.9rem;"><?= !empty($c['created_at']) ? date('d.m.Y', strtotime($c['created_at'])) : '—' ?></td>
                        <td style="padding: 0.75rem 1rem;"><a href="<?= $baseUrl ?>/admin/customers/show?id=<?= (int) $c['id'] ?>">Detay</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

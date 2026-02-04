<?php $c = $customer; ?>
<p style="margin-bottom: 1rem;"><a href="<?= $baseUrl ?>/admin/customers">← Müşteri listesine dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Müşteri: <?= htmlspecialchars(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) ?></h1>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">İletişim</h2>
        <p style="margin: 0 0 0.25rem;"><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a></p>
        <p style="margin: 0 0 0.25rem;"><?= htmlspecialchars($c['phone'] ?? '—') ?></p>
        <p style="margin: 0; font-size: 0.9rem; color: #666;">Kayıt: <?= !empty($c['created_at']) ? date('d.m.Y H:i', strtotime($c['created_at'])) : '—' ?></p>
        <p style="margin: 0.25rem 0 0;">Durum: <?= (int)($c['is_active'] ?? 1) === 1 ? 'Aktif' : 'Pasif' ?></p>
    </div>
</div>

<?php if (!empty($addresses)): ?>
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Kayıtlı adresler</h2>
        <ul style="margin: 0; padding-left: 1.25rem;">
            <?php foreach ($addresses as $a): ?>
                <li style="margin-bottom: 0.5rem;">
                    <?= htmlspecialchars(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))) ?>,
                    <?= htmlspecialchars($a['address_line']) ?>,
                    <?= htmlspecialchars(($a['district'] ?? '') . ' / ' . ($a['city'] ?? '')) ?><?= !empty($a['postal_code']) ? ' ' . htmlspecialchars($a['postal_code']) : '' ?>,
                    <?= htmlspecialchars($a['phone'] ?? '') ?>
                    <?php if ((int)($a['is_default']) === 1): ?> <span style="font-size: 0.8rem; background: #27ae60; color: #fff; padding: 0.1rem 0.35rem; border-radius: 4px;">Varsayılan</span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
    <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Sipariş geçmişi</h2>
    <?php if (empty($orders)): ?>
        <p style="margin: 0; color: #666;">Henüz sipariş yok.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; min-width: 500px; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">Sipariş no</th>
                        <th style="text-align: right; padding: 0.5rem; border-bottom: 1px solid #eee;">Toplam</th>
                        <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">Durum</th>
                        <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">Tarih</th>
                        <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.5rem;"><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                            <td style="padding: 0.5rem; text-align: right;"><?= number_format((float) $o['total'], 2, ',', '.') ?> ₺</td>
                            <td style="padding: 0.5rem;"><?= $statusLabels[$o['status']] ?? $o['status'] ?></td>
                            <td style="padding: 0.5rem; font-size: 0.9rem;"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                            <td style="padding: 0.5rem;"><a href="<?= $baseUrl ?>/admin/orders/show?id=<?= (int) $o['id'] ?>">Detay</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

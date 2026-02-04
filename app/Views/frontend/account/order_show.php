<?php $o = $order; ?>
<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="color: #666;">Hesabım</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/siparisler" style="color: #666;">Siparişlerim</a>
    <span style="color: #999;"> / </span>
    <span>Sipariş <?= htmlspecialchars($o['order_number']) ?></span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Sipariş <?= htmlspecialchars($o['order_number']) ?></h1>
<p style="margin: 0 0 1rem; color: #666;">Durum: <strong><?= $statusLabels[$o['status']] ?? $o['status'] ?></strong> — Tarih: <?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></p>

<div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
    <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Teslimat adresi</h2>
    <p style="margin: 0;"><?= htmlspecialchars(trim(($o['shipping_first_name'] ?? '') . ' ' . ($o['shipping_last_name'] ?? ''))) ?></p>
    <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars($o['shipping_address_line'] ?? '') ?></p>
    <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars(($o['shipping_district'] ?? '') . ' / ' . ($o['shipping_city'] ?? '')) ?><?= !empty($o['shipping_postal_code']) ? ' ' . htmlspecialchars($o['shipping_postal_code']) : '' ?></p>
    <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars($o['shipping_phone'] ?? '') ?></p>
</div>

<div style="overflow-x: auto; margin-bottom: 1.5rem;">
    <table style="width: 100%; min-width: 400px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ürün</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Adet</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Birim fiyat</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Toplam</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= (int) $item['quantity'] ?></td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format((float) $item['price'], 2, ',', '.') ?> ₺</td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format((float) $item['total'], 2, ',', '.') ?> ₺</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p style="margin-bottom: 0.5rem;">Ara toplam: <?= number_format((float) $o['subtotal'], 2, ',', '.') ?> ₺ — Kargo: <?= number_format((float) $o['shipping_cost'], 2, ',', '.') ?> ₺</p>
<p style="margin: 0 0 1rem; font-weight: bold;">Toplam: <?= number_format((float) $o['total'], 2, ',', '.') ?> ₺</p>

<?php if (!empty($shipments)): ?>
    <div style="background: #f0f7ff; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #1a4d6d;">Kargo bilgisi</h2>
        <?php foreach ($shipments as $s): ?>
            <p style="margin: 0.25rem 0;">
                <?= htmlspecialchars($s['carrier'] ?? 'Kargo') ?> — Takip no: <strong><?= htmlspecialchars($s['tracking_number'] ?? '—') ?></strong>
                <?= $s['shipped_at'] ? ' (Kargoya veriliş: ' . date('d.m.Y', strtotime($s['shipped_at'])) . ')' : '' ?>
            </p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p><a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/siparisler">← Siparişlerime dön</a></p>

<?php
$paymentLabels = ['cod' => 'Kapıda ödeme', 'bank_transfer' => 'Havale/EFT', 'stripe' => 'Kredi kartı (Stripe)'];
$o = $order;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş fişi - <?= htmlspecialchars($o['order_number']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #222; max-width: 800px; margin: 0 auto; padding: 1rem; }
        .no-print { margin-bottom: 1rem; }
        .no-print a { display: inline-block; padding: 0.5rem 1rem; background: #333; color: #fff; text-decoration: none; border-radius: 4px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
        h1 { font-size: 1.25rem; margin: 0 0 1rem; border-bottom: 1px solid #ccc; padding-bottom: 0.5rem; }
        .row { display: flex; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 1rem; }
        .col { flex: 1; min-width: 200px; }
        .block { margin-bottom: 1rem; }
        .block h2 { font-size: 0.85rem; color: #555; margin: 0 0 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { text-align: left; padding: 0.4rem 0.5rem; border-bottom: 1px solid #eee; }
        th { background: #f5f5f5; font-size: 0.85rem; }
        .text-right { text-align: right; }
        .totals { max-width: 280px; margin-left: auto; }
        .totals tr:last-child { font-weight: bold; }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="<?= $baseUrl ?>/admin/orders/show?id=<?= (int) $o['id'] ?>">← Siparişe dön</a>
        <button type="button" onclick="window.print();" style="margin-left: 0.5rem; padding: 0.5rem 1rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Yazdır</button>
    </div>

    <h1>Sipariş fişi – <?= htmlspecialchars($o['order_number']) ?></h1>
    <p style="margin: 0 0 1rem; color: #666;">Tarih: <?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></p>

    <div class="row">
        <div class="col block">
            <h2>Müşteri</h2>
            <p style="margin: 0;"><?= htmlspecialchars(trim(($o['guest_first_name'] ?? '') . ' ' . ($o['guest_last_name'] ?? ''))) ?: '—' ?></p>
            <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars($o['guest_email'] ?? '—') ?></p>
            <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars($o['guest_phone'] ?? '—') ?></p>
        </div>
        <div class="col block">
            <h2>Teslimat adresi</h2>
            <p style="margin: 0;"><?= htmlspecialchars(trim(($o['shipping_first_name'] ?? '') . ' ' . ($o['shipping_last_name'] ?? ''))) ?></p>
            <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars($o['shipping_address_line'] ?? '') ?></p>
            <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars(($o['shipping_district'] ?? '') . ' / ' . ($o['shipping_city'] ?? '')) ?><?= !empty($o['shipping_postal_code']) ? ' ' . htmlspecialchars($o['shipping_postal_code']) : '' ?></p>
            <p style="margin: 0.25rem 0 0;"><?= htmlspecialchars($o['shipping_phone'] ?? '') ?></p>
        </div>
    </div>

    <div class="block">
        <h2>Ödeme</h2>
        <p style="margin: 0;"><?= $paymentLabels[$o['payment_method']] ?? $o['payment_method'] ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ürün</th>
                <th>SKU</th>
                <th class="text-right">Adet</th>
                <th class="text-right">Birim fiyat</th>
                <th class="text-right">Toplam</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= htmlspecialchars($item['product_sku']) ?></td>
                    <td class="text-right"><?= (int) $item['quantity'] ?></td>
                    <td class="text-right"><?= number_format((float) $item['price'], 2, ',', '.') ?> ₺</td>
                    <td class="text-right"><?= number_format((float) $item['total'], 2, ',', '.') ?> ₺</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Ara toplam</td><td class="text-right"><?= number_format((float) $o['subtotal'], 2, ',', '.') ?> ₺</td></tr>
        <tr><td>Kargo</td><td class="text-right"><?= number_format((float) $o['shipping_cost'], 2, ',', '.') ?> ₺</td></tr>
        <?php if ((float) ($o['discount_amount'] ?? 0) > 0): ?>
            <tr><td>İndirim</td><td class="text-right">-<?= number_format((float) $o['discount_amount'], 2, ',', '.') ?> ₺</td></tr>
        <?php endif; ?>
        <tr><td>Toplam</td><td class="text-right"><?= number_format((float) $o['total'], 2, ',', '.') ?> ₺</td></tr>
    </table>

    <?php if (!empty($o['customer_notes'])): ?>
        <div class="block">
            <h2>Müşteri notu</h2>
            <p style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($o['customer_notes']) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($shipments)): ?>
        <div class="block">
            <h2>Kargo</h2>
            <table>
                <thead>
                    <tr><th>Firma</th><th>Takip no</th><th>Kargoya veriliş</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($shipments as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['carrier'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($s['tracking_number'] ?? '—') ?></td>
                            <td><?= $s['shipped_at'] ? date('d.m.Y H:i', strtotime($s['shipped_at'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</body>
</html>

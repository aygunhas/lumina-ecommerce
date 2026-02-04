<p style="margin-bottom: 1rem;"><a href="<?= $baseUrl ?>/admin/orders">← Sipariş listesine dön</a></p>

<?php if (!empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Sipariş durumu güncellendi.</p>
<?php endif; ?>
<?php if (!empty($_GET['shipment'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Kargo bilgisi eklendi.</p>
<?php endif; ?>

<?php
$paymentLabels = ['cod' => 'Kapıda ödeme', 'bank_transfer' => 'Havale/EFT', 'stripe' => 'Kredi kartı (Stripe)'];
$paymentStatusLabels = ['pending' => 'Beklemede', 'paid' => 'Ödendi', 'failed' => 'Başarısız', 'refunded' => 'İade edildi'];
$o = $order;
?>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Sipariş <?= htmlspecialchars($o['order_number']) ?>
    <a href="<?= $baseUrl ?>/admin/orders/print?id=<?= (int) $o['id'] ?>" target="_blank" rel="noopener" style="margin-left: 0.75rem; font-size: 0.85rem; font-weight: normal; padding: 0.35rem 0.75rem; background: #333; color: #fff; text-decoration: none; border-radius: 4px;">Yazdır</a>
</h1>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Müşteri bilgisi</h2>
        <p style="margin: 0 0 0.25rem;"><?= htmlspecialchars(trim(($o['guest_first_name'] ?? '') . ' ' . ($o['guest_last_name'] ?? ''))) ?: '—' ?></p>
        <p style="margin: 0 0 0.25rem;"><a href="mailto:<?= htmlspecialchars($o['guest_email'] ?? '') ?>"><?= htmlspecialchars($o['guest_email'] ?? '—') ?></a></p>
        <p style="margin: 0;"><?= htmlspecialchars($o['guest_phone'] ?? '—') ?></p>
    </div>
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Teslimat adresi</h2>
        <p style="margin: 0 0 0.25rem;"><?= htmlspecialchars(trim(($o['shipping_first_name'] ?? '') . ' ' . ($o['shipping_last_name'] ?? ''))) ?></p>
        <p style="margin: 0 0 0.25rem;"><?= htmlspecialchars($o['shipping_address_line'] ?? '') ?></p>
        <p style="margin: 0 0 0.25rem;"><?= htmlspecialchars(($o['shipping_district'] ?? '') . ' / ' . ($o['shipping_city'] ?? '')) ?><?= !empty($o['shipping_postal_code']) ? ' ' . htmlspecialchars($o['shipping_postal_code']) : '' ?></p>
        <p style="margin: 0;"><?= htmlspecialchars($o['shipping_phone'] ?? '') ?></p>
    </div>
</div>

<div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
    <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Ödeme</h2>
    <p style="margin: 0 0 0.25rem;">Yöntem: <?= $paymentLabels[$o['payment_method']] ?? $o['payment_method'] ?></p>
    <p style="margin: 0;">Ödeme durumu: <?= $paymentStatusLabels[$o['payment_status']] ?? $o['payment_status'] ?></p>
</div>

<div style="overflow-x: auto; margin-bottom: 1.5rem;">
    <table style="width: 100%; min-width: 500px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ürün</th>
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">SKU</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Adet</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Birim fiyat</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Toplam</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($item['product_sku']) ?></td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= (int) $item['quantity'] ?></td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format((float) $item['price'], 2, ',', '.') ?> ₺</td>
                    <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format((float) $item['total'], 2, ',', '.') ?> ₺</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div style="max-width: 320px; margin-left: auto; margin-bottom: 1.5rem;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr><td style="padding: 0.25rem 0;">Ara toplam</td><td style="padding: 0.25rem 0; text-align: right;"><?= number_format((float) $o['subtotal'], 2, ',', '.') ?> ₺</td></tr>
        <tr><td style="padding: 0.25rem 0;">Kargo</td><td style="padding: 0.25rem 0; text-align: right;"><?= number_format((float) $o['shipping_cost'], 2, ',', '.') ?> ₺</td></tr>
        <?php if ((float) ($o['discount_amount'] ?? 0) > 0): ?>
            <tr><td style="padding: 0.25rem 0;">İndirim</td><td style="padding: 0.25rem 0; text-align: right;">-<?= number_format((float) $o['discount_amount'], 2, ',', '.') ?> ₺</td></tr>
        <?php endif; ?>
        <tr style="font-weight: bold;"><td style="padding: 0.5rem 0;">Toplam</td><td style="padding: 0.5rem 0; text-align: right;"><?= number_format((float) $o['total'], 2, ',', '.') ?> ₺</td></tr>
    </table>
</div>

<?php if (!empty($o['customer_notes'])): ?>
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
        <h2 style="margin: 0 0 0.5rem; font-size: 1rem; color: #666;">Müşteri notu</h2>
        <p style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($o['customer_notes']) ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($o['admin_notes'])): ?>
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
        <h2 style="margin: 0 0 0.5rem; font-size: 1rem; color: #666;">Admin notu</h2>
        <p style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($o['admin_notes']) ?></p>
    </div>
<?php endif; ?>

<?php
$canAddShipment = !in_array($o['status'] ?? '', ['cancelled', 'refunded'], true);
$shipments = $shipments ?? [];
?>
<?php if (!empty($shipments)): ?>
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Kargo bilgisi</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">Kargo firması</th>
                    <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">Takip numarası</th>
                    <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">Kargoya veriliş</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shipments as $s): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.5rem;"><?= htmlspecialchars($s['carrier'] ?? '—') ?></td>
                        <td style="padding: 0.5rem;"><?= htmlspecialchars($s['tracking_number'] ?? '—') ?></td>
                        <td style="padding: 0.5rem;"><?= $s['shipped_at'] ? date('d.m.Y H:i', strtotime($s['shipped_at'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($canAddShipment): ?>
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Kargo bilgisi ekle</h2>
        <form method="post" action="<?= $baseUrl ?>/admin/orders/add-shipment" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem;">
            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
            <div>
                <label for="carrier" style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Kargo firması</label>
                <input type="text" id="carrier" name="carrier" placeholder="Örn: Yurtiçi, Aras" style="padding: 0.5rem; width: 160px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div>
                <label for="tracking_number" style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Takip numarası</label>
                <input type="text" id="tracking_number" name="tracking_number" placeholder="Takip no" style="padding: 0.5rem; width: 180px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <?php if (!in_array($o['status'] ?? '', ['shipped', 'delivered'], true)): ?>
                <div style="display: flex; align-items: center; padding-bottom: 0.25rem;">
                    <input type="checkbox" id="set_status_shipped" name="set_status_shipped" value="1" checked style="margin-right: 0.5rem;">
                    <label for="set_status_shipped" style="font-size: 0.9rem;">Sipariş durumunu &quot;Kargoda&quot; yap</label>
                </div>
            <?php endif; ?>
            <button type="submit" style="padding: 0.5rem 1rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Kargo ekle</button>
        </form>
    </div>
<?php endif; ?>

<div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem;">
    <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Sipariş durumunu güncelle</h2>
    <form method="post" action="<?= $baseUrl ?>/admin/orders/update-status" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem;">
        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
        <div>
            <label for="status" style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Durum</label>
            <select id="status" name="status" required style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value) ?>" <?= ($o['status'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label for="status_note" style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Not (isteğe bağlı)</label>
            <input type="text" id="status_note" name="status_note" placeholder="Örn: Kargoya verildi" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <button type="submit" style="padding: 0.5rem 1rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Durumu güncelle</button>
    </form>
</div>

<?php if (!empty($statusHistory)): ?>
    <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <h2 style="margin: 0 0 0.75rem; font-size: 1rem; color: #666;">Durum geçmişi</h2>
        <ul style="margin: 0; padding-left: 1.25rem;">
            <?php foreach ($statusHistory as $h): ?>
                <li style="margin-bottom: 0.25rem;">
                    <strong><?= htmlspecialchars($statusOptions[$h['status']] ?? $h['status']) ?></strong>
                    <?= date('d.m.Y H:i', strtotime($h['created_at'])) ?>
                    <?php if (!empty($h['note'])): ?> — <?= htmlspecialchars($h['note']) ?><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<p style="margin-top: 1.5rem;"><a href="<?= $baseUrl ?>/admin/orders">← Sipariş listesine dön</a></p>

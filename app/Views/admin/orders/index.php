<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Siparişler</h1>

<form method="get" action="<?= htmlspecialchars($baseUrl) ?>/admin/orders" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem; margin-bottom: 1.5rem; padding: 1rem; background: #f9f9f9; border-radius: 8px;">
    <div>
        <label for="q" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Ara (sipariş no, müşteri, e-posta)</label>
        <input type="text" id="q" name="q" value="<?= htmlspecialchars($filterQ ?? '') ?>" placeholder="Ara..." style="padding: 0.5rem; width: 220px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div>
        <label for="status" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Durum</label>
        <select id="status" name="status" style="padding: 0.5rem; width: 140px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">— Tümü —</option>
            <?php foreach (($statusOptions ?? []) as $val => $label): ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= ($filterStatus ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="date_from" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Tarih (başlangıç)</label>
        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($filterDateFrom ?? '') ?>" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div>
        <label for="date_to" style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; color: #666;">Tarih (bitiş)</label>
        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($filterDateTo ?? '') ?>" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div>
        <button type="submit" style="padding: 0.5rem 1rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Filtrele</button>
        <?php if (($filterQ ?? '') !== '' || ($filterStatus ?? '') !== '' || ($filterDateFrom ?? '') !== '' || ($filterDateTo ?? '') !== ''): ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders" style="margin-left: 0.5rem; font-size: 0.9rem; color: #666;">Temizle</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($orders)): ?>
    <p style="color: #666;">Henüz sipariş yok. Mağazadan verilen siparişler burada listelenir.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 700px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Sipariş no</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Müşteri</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Toplam</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ödeme</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Tarih</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <?php
                    $customer = trim(($o['guest_first_name'] ?? '') . ' ' . ($o['guest_last_name'] ?? ''));
                    if ($customer === '') {
                        $customer = $o['guest_email'] ?? '—';
                    }
                    $paymentLabels = ['cod' => 'Kapıda', 'bank_transfer' => 'Havale/EFT', 'stripe' => 'Kredi kartı'];
                    $statusLabels = ['pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'processing' => 'Hazırlanıyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim edildi', 'cancelled' => 'İptal', 'refunded' => 'İade'];
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($customer) ?><br><span style="font-size: 0.85rem; color: #666;"><?= htmlspecialchars($o['guest_email'] ?? '') ?></span></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format((float) $o['total'], 2, ',', '.') ?> ₺</td>
                        <td style="padding: 0.75rem 1rem;"><?= $paymentLabels[$o['payment_method']] ?? $o['payment_method'] ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= $statusLabels[$o['status']] ?? $o['status'] ?></td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.9rem;"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                        <td style="padding: 0.75rem 1rem;"><a href="<?= $baseUrl ?>/admin/orders/show?id=<?= (int) $o['id'] ?>">Detay</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

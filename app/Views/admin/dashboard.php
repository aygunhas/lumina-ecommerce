<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Kontrol paneli</h1>
<div class="cards" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
    <div class="card" style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <strong style="display: block; font-size: 1.75rem; color: #333;"><?= $stats['orders_today'] ?></strong>
        <span style="font-size: 0.9rem; color: #666;">Bugünkü siparişler</span>
    </div>
    <div class="card" style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <strong style="display: block; font-size: 1.75rem; color: #333;"><?= $stats['orders_total'] ?></strong>
        <span style="font-size: 0.9rem; color: #666;">Toplam sipariş</span>
    </div>
    <div class="card" style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <strong style="display: block; font-size: 1.75rem; color: #333;"><?= $stats['products_total'] ?></strong>
        <span style="font-size: 0.9rem; color: #666;">Ürün sayısı</span>
    </div>
    <div class="card" style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
        <strong style="display: block; font-size: 1.75rem; color: #333;"><?= $stats['users_total'] ?></strong>
        <span style="font-size: 0.9rem; color: #666;">Üye sayısı</span>
    </div>
</div>

<?php if (!empty($chartData)): ?>
    <section style="margin-top: 2rem;">
        <h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Son 30 gün satış grafiği</h2>
        <div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow-x: auto;">
            <div style="display: flex; align-items: flex-end; gap: 4px; min-height: 180px; padding: 0.5rem 0;">
                <?php foreach ($chartData as $row): ?>
                    <?php
                    $dayTotal = (float) $row['total'];
                    $heightPct = $chartMax > 0 ? round(($dayTotal / $chartMax) * 100) : 0;
                    if ($heightPct > 0 && $heightPct < 5) {
                        $heightPct = 5;
                    }
                    $dayLabel = date('d.m', strtotime($row['day']));
                    ?>
                    <div style="flex: 1; min-width: 24px; max-width: 40px; display: flex; flex-direction: column; align-items: center;" title="<?= htmlspecialchars($dayLabel) ?>: <?= number_format($dayTotal, 2, ',', '.') ?> ₺ (<?= (int)$row['count'] ?> sipariş)">
                        <div style="width: 100%; height: <?= $heightPct ?>%; min-height: <?= $heightPct > 0 ? '20px' : '0' ?>; background: #3498db; border-radius: 4px 4px 0 0;"></div>
                        <span style="font-size: 0.65rem; color: #666; margin-top: 4px; white-space: nowrap;"><?= $dayLabel ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p style="margin: 0.75rem 0 0; font-size: 0.85rem; color: #666;">Günlük satış tutarı (₺). İptal ve iade hariç.</p>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($lowStockProducts)): ?>
    <section style="margin-top: 2rem;">
        <h2 style="margin: 0 0 1rem; font-size: 1.2rem; color: #b45309;">⚠️ Düşük stok uyarısı</h2>
        <p style="margin: 0 0 0.75rem; font-size: 0.9rem; color: #666;">Stok eşiğinin altındaki ürünler (stok tükenmeden önce tedarik edin).</p>
        <div style="overflow-x: auto;">
            <table style="width: 100%; min-width: 400px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
                <thead>
                    <tr style="background: #fff8e6;">
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Ürün</th>
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">SKU</th>
                        <th style="text-align: right; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Stok</th>
                        <th style="text-align: right; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Eşik</th>
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockProducts as $p): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.6rem 0.75rem; font-size: 0.9rem;"><?= htmlspecialchars($p['name']) ?></td>
                            <td style="padding: 0.6rem 0.75rem; font-size: 0.9rem;"><?= htmlspecialchars($p['sku'] ?? '—') ?></td>
                            <td style="padding: 0.6rem 0.75rem; text-align: right; font-size: 0.9rem; <?= (int)$p['stock'] === 0 ? 'color: #c62828; font-weight: bold;' : '' ?>"><?= (int) $p['stock'] ?></td>
                            <td style="padding: 0.6rem 0.75rem; text-align: right; font-size: 0.9rem;"><?= (int) ($p['low_stock_threshold'] ?? 5) ?></td>
                            <td style="padding: 0.6rem 0.75rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) $p['id'] ?>">Düzenle</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top: 0.75rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/products">Tüm ürünler →</a></p>
    </section>
<?php endif; ?>

<?php if (!empty($recentOrders)): ?>
    <section style="margin-top: 2rem;">
        <h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Son siparişler</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; min-width: 600px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Sipariş no</th>
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Müşteri</th>
                        <th style="text-align: right; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Toplam</th>
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Durum</th>
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">Tarih</th>
                        <th style="text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; font-size: 0.9rem;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $paymentLabels = ['cod' => 'Kapıda', 'bank_transfer' => 'Havale/EFT', 'stripe' => 'Kredi kartı'];
                    $statusLabels = ['pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'processing' => 'Hazırlanıyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim edildi', 'cancelled' => 'İptal', 'refunded' => 'İade'];
                    foreach ($recentOrders as $o):
                        $customer = trim(($o['guest_first_name'] ?? '') . ' ' . ($o['guest_last_name'] ?? ''));
                        if ($customer === '') {
                            $customer = $o['guest_email'] ?? '—';
                        }
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.6rem 0.75rem; font-size: 0.9rem;"><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                            <td style="padding: 0.6rem 0.75rem; font-size: 0.9rem;"><?= htmlspecialchars($customer) ?></td>
                            <td style="padding: 0.6rem 0.75rem; text-align: right; font-size: 0.9rem;"><?= number_format((float) $o['total'], 2, ',', '.') ?> ₺</td>
                            <td style="padding: 0.6rem 0.75rem; font-size: 0.9rem;"><?= $statusLabels[$o['status']] ?? $o['status'] ?></td>
                            <td style="padding: 0.6rem 0.75rem; font-size: 0.85rem;"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                            <td style="padding: 0.6rem 0.75rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders/show?id=<?= (int) $o['id'] ?>">Detay</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top: 0.75rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders">Tüm siparişler →</a></p>
    </section>
<?php endif; ?>

<section class="proje-durumu" style="margin-top: 2rem; padding: 1.25rem; background: #f0f7ff; border: 1px solid #b8d4e8; border-radius: 8px; font-size: 0.95rem;">
    <h2 style="margin: 0 0 0.75rem; font-size: 1.1rem; color: #1a4d6d;">📋 Şu an ne var? Nasıl test edebilirsiniz?</h2>
    <ul style="margin: 0; padding-left: 1.25rem; line-height: 1.6; color: #333;">
        <li><strong>Bu sayfa:</strong> Kontrol paneli (dashboard). Özet kartlar ve son siparişler; sol menüden Kategoriler, Ürünler, Siparişler, İletişim mesajları.</li>
        <li><strong>Mağaza:</strong> <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #1a4d6d;">Mağazayı aç</a> ile anasayfa, kategoriler, ürünler, sepet, ödeme.</li>
    </ul>
    <p style="margin: 0.75rem 0 0; font-size: 0.9rem; color: #555;">Detaylı adımlar: <code>docs/TEST_VE_YONLENDIRME.md</code></p>
</section>

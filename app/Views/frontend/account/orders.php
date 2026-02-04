<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="color: #666;">Hesabım</a>
    <span style="color: #999;"> / </span>
    <span>Siparişlerim</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Siparişlerim</h1>

<?php if (empty($orders)): ?>
    <p style="color: #666;">Henüz siparişiniz yok. <a href="<?= htmlspecialchars($baseUrl) ?>/">Alışverişe başlayın</a>.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 500px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Sipariş no</th>
                    <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Toplam</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Tarih</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                        <td style="padding: 0.75rem 1rem; text-align: right;"><?= number_format((float) $o['total'], 2, ',', '.') ?> ₺</td>
                        <td style="padding: 0.75rem 1rem;"><?= $statusLabels[$o['status']] ?? $o['status'] ?></td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.9rem;"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                        <td style="padding: 0.75rem 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/hesabim/siparisler/show?id=<?= (int) $o['id'] ?>">Detay</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<p style="margin-top: 1rem;"><a href="<?= htmlspecialchars($baseUrl) ?>/hesabim">← Hesabıma dön</a></p>

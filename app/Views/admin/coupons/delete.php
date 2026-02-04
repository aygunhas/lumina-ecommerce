<?php $c = $coupon; ?>
<p style="margin-bottom: 1rem;"><a href="<?= $baseUrl ?>/admin/coupons">← Kupon listesine dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Kuponu sil</h1>
<p style="margin: 0 0 1rem;">Bu kuponu silmek istediğinize emin misiniz? Kupon kodu: <strong><?= htmlspecialchars($c['code']) ?></strong></p>
<p style="margin: 0 0 1rem; color: #666;">Bu kupon <?= (int) $c['used_count'] ?> kez kullanılmış. Silindiğinde siparişlerdeki kupon bilgisi (coupon_id) NULL kalır.</p>
<form method="post" action="<?= $baseUrl ?>/admin/coupons/delete?id=<?= (int) $c['id'] ?>">
    <button type="submit" style="padding: 0.5rem 1rem; background: #c0392b; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Evet, sil</button>
    <a href="<?= $baseUrl ?>/admin/coupons" style="margin-left: 1rem; color: #666;">İptal</a>
</form>

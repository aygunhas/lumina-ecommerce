<p style="margin-bottom: 1rem;"><a href="<?= $baseUrl ?>/admin/contact-messages">← Mesaj listesine dön</a></p>

<?php $m = $message; ?>
<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Mesaj #<?= (int) $m['id'] ?></h1>

<div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1rem;">
    <p style="margin: 0 0 0.5rem;"><strong>Gönderen:</strong> <?= htmlspecialchars($m['name']) ?></p>
    <p style="margin: 0 0 0.5rem;"><strong>E-posta:</strong> <a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></p>
    <?php if (!empty($m['phone'])): ?>
        <p style="margin: 0 0 0.5rem;"><strong>Telefon:</strong> <?= htmlspecialchars($m['phone']) ?></p>
    <?php endif; ?>
    <?php if (!empty($m['subject'])): ?>
        <p style="margin: 0 0 0.5rem;"><strong>Konu:</strong> <?= htmlspecialchars($m['subject']) ?></p>
    <?php endif; ?>
    <p style="margin: 0 0 0.5rem;"><strong>Tarih:</strong> <?= $m['created_at'] ? date('d.m.Y H:i', strtotime($m['created_at'])) : '—' ?></p>
</div>

<div style="background: #fff; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1rem;">
    <h2 style="margin: 0 0 0.5rem; font-size: 1rem; color: #666;">Mesaj</h2>
    <p style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($m['message']) ?></p>
</div>

<p><a href="<?= $baseUrl ?>/admin/contact-messages">← Mesaj listesine dön</a></p>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">İletişim mesajları</h1>

<?php if (empty($messages)): ?>
    <p style="color: #666;">Henüz iletişim formundan mesaj gelmedi. Mağaza /iletisim sayfasından gönderilen mesajlar burada listelenir.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 600px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Gönderen</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">E-posta</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Konu</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Tarih</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                    <tr style="border-bottom: 1px solid #eee; <?= !(int)($m['is_read'] ?? 0) ? 'background: #f9f9f9;' : '' ?>">
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($m['name']) ?></td>
                        <td style="padding: 0.75rem 1rem;"><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></td>
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($m['subject'] ?? '—') ?></td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.9rem;"><?= $m['created_at'] ? date('d.m.Y H:i', strtotime($m['created_at'])) : '—' ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= (int)($m['is_read'] ?? 0) ? 'Okundu' : 'Yeni' ?></td>
                        <td style="padding: 0.75rem 1rem;"><a href="<?= $baseUrl ?>/admin/contact-messages/show?id=<?= (int) $m['id'] ?>">Görüntüle</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

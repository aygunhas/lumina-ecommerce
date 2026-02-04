<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
    <h1 style="margin: 0; font-size: 1.5rem;">Sayfalar</h1>
    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pages/create" style="display: inline-block; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Yeni sayfa</a>
</div>

<?php if (!empty($_GET['created'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Sayfa oluşturuldu.</p>
<?php endif; ?>
<?php if (!empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Sayfa güncellendi.</p>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Sayfa silindi.</p>
<?php endif; ?>

<?php if (empty($pages)): ?>
    <p style="color: #666;">Henüz sayfa yok. "Yeni sayfa" ile ekleyebilirsiniz. SSS, İade koşulları, KVKK, Mesafeli satış sözleşmesi gibi sayfalar buradan yönetilir.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 600px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Slug</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Başlık</th>
                    <th style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Sıra</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $p): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><code style="font-size: 0.85rem;"><?= htmlspecialchars($p['slug']) ?></code></td>
                        <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($p['title']) ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: center;"><?= (int) $p['sort_order'] ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= (int) $p['is_active'] === 1 ? 'Aktif' : 'Pasif' ?></td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/sayfa/<?= htmlspecialchars($p['slug']) ?>" target="_blank" style="margin-right: 0.5rem;">Görüntüle</a>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pages/edit?id=<?= (int) $p['id'] ?>">Düzenle</a>
                            <span style="color: #ccc;">|</span>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pages/delete?id=<?= (int) $p['id'] ?>" style="color: #c0392b;" onclick="return confirm('Bu sayfayı silmek istediğinize emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

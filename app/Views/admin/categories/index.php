<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
    <h1 style="margin: 0; font-size: 1.5rem;">Kategoriler</h1>
    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories/create" style="display: inline-block; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Yeni kategori</a>
</div>

<?php if (empty($categories)): ?>
    <p style="color: #666;">Henüz kategori yok. "Yeni kategori" ile ekleyebilirsiniz.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Ad</th>
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Üst kategori</th>
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Sıra</th>
                <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                <th style="text-align: right; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.75rem 1rem;"><?= htmlspecialchars($c['name']) ?></td>
                    <td style="padding: 0.75rem 1rem;"><?= $c['parent_name'] ? htmlspecialchars($c['parent_name']) : '—' ?></td>
                    <td style="padding: 0.75rem 1rem;"><?= (int) $c['sort_order'] ?></td>
                    <td style="padding: 0.75rem 1rem;"><?= (int) $c['is_active'] ? 'Aktif' : 'Pasif' ?></td>
                    <td style="padding: 0.75rem 1rem; text-align: right;">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories/edit?id=<?= (int) $c['id'] ?>" style="color: #3498db; text-decoration: none;">Düzenle</a>
                        <span style="color: #ccc;">|</span>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories/delete?id=<?= (int) $c['id'] ?>" style="color: #c0392b; text-decoration: none;">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

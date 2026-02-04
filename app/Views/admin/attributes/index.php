<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
    <h1 style="margin: 0; font-size: 1.5rem;">Özellikler (Beden / Renk)</h1>
    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes/create" style="display: inline-block; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Yeni özellik</a>
</div>

<?php if (!empty($_GET['created'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Özellik oluşturuldu.</p>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Özellik silindi.</p>
<?php endif; ?>

<?php if (empty($attributes)): ?>
    <p style="color: #666;">Henüz özellik yok. "Yeni özellik" ile Beden veya Renk ekleyin; ardından değerlerini (S, M, L / Kırmızı, Mavi vb.) düzenlemeden ekleyin. Veya <code>php database/seeds/seed_attributes.php</code> ile örnek veri ekleyebilirsiniz.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 400px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Özellik</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Tip</th>
                    <th style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Değer sayısı</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $typeLabels = ['size' => 'Beden', 'color' => 'Renk', 'other' => 'Diğer'];
                foreach ($attributes as $a):
                ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;"><strong><?= htmlspecialchars($a['name']) ?></strong> <code style="font-size: 0.8rem;"><?= htmlspecialchars($a['slug']) ?></code></td>
                        <td style="padding: 0.75rem 1rem;"><?= $typeLabels[$a['type']] ?? $a['type'] ?></td>
                        <td style="padding: 0.75rem 1rem; text-align: center;"><?= (int) $a['values_count'] ?></td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes/edit?id=<?= (int) $a['id'] ?>">Düzenle (değerler)</a>
                            <span style="color: #ccc;">|</span>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes/delete?id=<?= (int) $a['id'] ?>" style="color: #c0392b;" onclick="return confirm('Bu özelliği ve tüm değerlerini silmek istediğinize emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

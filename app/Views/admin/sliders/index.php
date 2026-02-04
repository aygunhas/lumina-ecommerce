<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
    <h1 style="margin: 0; font-size: 1.5rem;">Slider</h1>
    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/sliders/create" style="display: inline-block; padding: 0.5rem 1rem; background: #2c3e50; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">Yeni slider</a>
</div>

<?php if (!empty($_GET['created'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Slider eklendi.</p>
<?php endif; ?>
<?php if (!empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Slider güncellendi.</p>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Slider silindi.</p>
<?php endif; ?>

<?php if (empty($sliders)): ?>
    <p style="color: #666;">Henüz slider yok. Anasayfada slider göstermek için "Yeni slider" ile ekleyin.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 500px; border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Görsel</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Başlık / Alt başlık</th>
                    <th style="text-align: center; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Sıra</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">Durum</th>
                    <th style="text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #eee;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sliders as $s): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem 1rem;">
                            <img src="<?= htmlspecialchars($baseUrl) ?>/<?= htmlspecialchars($s['image']) ?>" alt="" style="max-width: 120px; max-height: 60px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <strong><?= htmlspecialchars($s['title'] ?? '—') ?></strong>
                            <?php if (!empty($s['subtitle'])): ?>
                                <br><span style="font-size: 0.85rem; color: #666;"><?= htmlspecialchars($s['subtitle']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;"><?= (int) $s['sort_order'] ?></td>
                        <td style="padding: 0.75rem 1rem;"><?= (int) $s['is_active'] === 1 ? 'Aktif' : 'Pasif' ?></td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/sliders/edit?id=<?= (int) $s['id'] ?>">Düzenle</a>
                            <span style="color: #ccc;">|</span>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/sliders/delete?id=<?= (int) $s['id'] ?>" style="color: #c0392b;" onclick="return confirm('Bu sliderı silmek istediğinize emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

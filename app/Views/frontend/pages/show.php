<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;"><?= htmlspecialchars($page['title']) ?></h1>

<?php if (!empty($page['content'])): ?>
    <div class="page-content" style="line-height: 1.6;">
        <?= nl2br(htmlspecialchars($page['content'])) ?>
    </div>
<?php else: ?>
    <p style="color: #666;">Bu sayfanın içeriği henüz eklenmemiş.</p>
<?php endif; ?>

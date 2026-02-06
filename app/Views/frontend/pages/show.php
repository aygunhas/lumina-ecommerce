<?php
$baseUrl = $baseUrl ?? '';
$page = $page ?? [];
$title = $page['title'] ?? 'Sayfa';
$content = $page['content'] ?? '';
?>
<div class="max-w-[1400px] mx-auto px-6 py-12 md:py-16">
    <header class="mb-10">
        <h1 class="font-display text-3xl md:text-4xl tracking-tight text-primary mb-4"><?= htmlspecialchars($title) ?></h1>
    </header>
    <?php if ($content !== ''): ?>
        <div class="max-w-2xl text-gray-600 text-sm leading-relaxed space-y-4 page-content">
            <?= nl2br(htmlspecialchars($content)) ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500 text-sm">Bu sayfanın içeriği henüz eklenmemiş.</p>
    <?php endif; ?>
</div>

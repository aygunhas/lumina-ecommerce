<?php
/**
 * Politika sayfaları şablonu (Gizlilik, İade, Mesafeli Satış vb.)
 * Beklenen değişkenler: $policyTitle, $updatedAt, $content, $navItems (opsiyonel), $baseUrl
 */
$baseUrl = $baseUrl ?? '';
$policyTitle = $policyTitle ?? 'GİZLİLİK POLİTİKASI';
$updatedAt = $updatedAt ?? '12 Ekim 2026';
$content = $content ?? '';
$navItems = $navItems ?? [];
?>
<style>
.policy-content h2,
.policy-content h3 { font-family: 'Cinzel', serif; letter-spacing: 0.025em; color: #000; margin-top: 2rem; margin-bottom: 1rem; scroll-margin-top: 6rem; }
.policy-content h2 { font-size: 1.25rem; }
.policy-content h3 { font-size: 1.125rem; }
.policy-content p { color: #4b5563; line-height: 1.75rem; margin-bottom: 1rem; }
.policy-content ul { list-style-type: disc; padding-left: 1.25rem; margin-bottom: 1rem; }
.policy-content li { margin-bottom: 0.5rem; color: #4b5563; line-height: 1.75rem; }
.policy-content a { color: #000; text-decoration: underline; }
.policy-content a:hover { color: #4b5563; }
</style>

<div class="max-w-7xl mx-auto px-6 pb-20">
    <div class="lg:flex lg:gap-12">
        <!-- Sticky Sidebar (masaüstü) -->
        <?php if (!empty($navItems)): ?>
        <aside class="hidden lg:block flex-shrink-0 w-52 pt-16">
            <nav class="sticky top-24 space-y-1" aria-label="Sayfa içeriği">
                <?php foreach ($navItems as $item): ?>
                <a href="#<?= htmlspecialchars($item['id']) ?>"
                   class="block text-xs text-gray-500 hover:text-black transition py-1.5 border-l-2 border-transparent hover:border-black pl-3 -ml-px">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <?php endif; ?>

        <div class="flex-1 min-w-0">
            <!-- Sayfa başlığı -->
            <header class="pt-16 pb-8 text-center">
                <h1 class="font-display text-2xl tracking-wide text-black"><?= htmlspecialchars($policyTitle) ?></h1>
                <?php if ($updatedAt !== ''): ?>
                <p class="text-xs text-gray-400 mt-4 mb-12">Son güncelleme: <?= htmlspecialchars($updatedAt) ?></p>
                <?php endif; ?>
            </header>

            <!-- Metin içeriği -->
            <div class="max-w-3xl mx-auto px-6 pb-20">
                <div class="policy-content prose prose-gray prose-sm md:prose-base max-w-none">
                    <?php if ($content !== ''): ?>
                        <?= $content ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">Bu sayfanın içeriği henüz eklenmemiş.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        if (anchor.getAttribute('href') === '#') return;
        anchor.addEventListener('click', function(e) {
            var id = this.getAttribute('href').slice(1);
            var el = id ? document.getElementById(id) : null;
            if (el) {
                e.preventDefault();
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
</script>

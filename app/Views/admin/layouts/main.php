<?php
$baseUrl = $baseUrl ?? '';
$currentUri = $currentUri ?? ($_SERVER['REQUEST_URI'] ?? '/');
if (($pos = strpos($currentUri, '?')) !== false) {
    $currentUri = substr($currentUri, 0, $pos);
}
$currentUri = rtrim($currentUri, '/') ?: '/';
$adminName = $adminName ?? 'Admin';
$notifData = function_exists('get_admin_notifications') ? get_admin_notifications() : ['notifications' => [], 'unread_count' => 0];
$isActive = function ($path) use ($currentUri) {
    if ($path === '/admin' || $path === '/admin/') {
        return $currentUri === '/admin' || $currentUri === '/admin/';
    }
    return strpos($currentUri, $path) === 0;
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>LUMINA PANEL</title>
    <link rel="icon" href="<?= $baseUrl ?>/assets/images/favicon.svg" type="image/svg+xml">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (Build) -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">
    <!-- Alpine.js (frontend ile aynı sıra) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>
<body class="bg-stone-100 font-sans text-stone-800" x-data="{ sidebarOpen: false }">
    <!-- Mobil sidebar overlay -->
    <div x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-black/50 md:hidden"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         aria-hidden="true"></div>

    <!-- SOL KOLON: SIDEBAR -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-stone-900 text-white transition-transform duration-200 ease-in-out md:translate-x-0"
           :class="{ 'translate-x-0': sidebarOpen }"
           x-show="true"
           aria-label="Panel menüsü">
        <div class="flex h-full flex-col">
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center justify-center border-b border-white/10">
                <span class="text-sm font-semibold tracking-widest">LUMINA PANEL</span>
            </div>

            <!-- Navigasyon -->
            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin') && $currentUri === '/admin' || $currentUri === '/admin/' ? 'bg-white/10 text-white' : 'text-stone-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/categories"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/categories') ? 'bg-white/10 text-white' : 'text-stone-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Kategoriler
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/products') ? 'bg-white/10 text-white' : 'text-stone-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Ürünler
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/orders') ? 'bg-white/10 text-white' : 'text-stone-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Siparişler
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/attributes"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/attributes') ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-2a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Özellikler (Varyant)
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/coupons"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/coupons') ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    Kuponlar
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/customers"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/customers') ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Müşteriler
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reports"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/reports') ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Raporlar
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pages"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/pages') ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Sayfalar
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/contact-messages"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/contact-messages') ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Mesajlar
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/settings"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors <?= $isActive('/admin/settings') ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Ayarlar
                </a>
            </nav>

            <!-- Alt: Mağazaya Dön + Çıkış -->
            <div class="shrink-0 border-t border-white/10 px-3 py-4 space-y-1">
                <a href="<?= htmlspecialchars($baseUrl) ?>/"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-stone-300 hover:bg-white/5 hover:text-white transition-colors">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V8a2 2 0 00-2-2h-4m-6-1l4 4m0 0l4-4m-4 4V10"/></svg>
                    Mağazaya Dön
                </a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/logout"
                   class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-stone-300 hover:bg-white/5 hover:text-white transition-colors">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Çıkış Yap
                </a>
            </div>
        </div>
        </aside>

    <!-- SAĞ KOLON: İÇERİK ALANI -->
    <div class="flex min-h-screen flex-col transition-all md:pl-64">
        <!-- Header (Üst Bar) -->
        <header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-stone-200 bg-[#FAFAF9] px-6">
            <button type="button"
                    @click="sidebarOpen = true"
                    class="rounded-md p-2 text-stone-600 hover:bg-stone-100 hover:text-stone-800 md:hidden"
                    aria-label="Menüyü aç">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="ml-auto flex items-center gap-2">
                <!-- Bildirim zili (navbar sağı, Admin yazısının solunda) -->
                <div class="relative" x-data="{ notifOpen: false }" @click.outside="notifOpen = false">
                    <button type="button"
                            @click="notifOpen = !notifOpen"
                            class="relative rounded-md p-2 text-stone-600 hover:bg-stone-100 hover:text-stone-800"
                            aria-label="Bildirimler"
                            aria-expanded="false"
                            :aria-expanded="notifOpen">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <?php if ($notifData['unread_count'] > 0): ?>
                        <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-medium text-white">
                            <?= $notifData['unread_count'] > 99 ? '99+' : (int) $notifData['unread_count'] ?>
                        </span>
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown -->
                    <div x-show="notifOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 rounded-md border border-stone-200 bg-[#FAFAF9] shadow-lg"
                         role="menu">
                        <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
                            <span class="text-sm font-semibold text-stone-800">Bildirimler</span>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/notifications?mark_all_read=1" class="text-xs text-stone-500 hover:text-stone-700">Tümünü Okundu Say</a>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <?php if (empty($notifData['notifications'])): ?>
                            <p class="px-4 py-6 text-center text-sm text-stone-500">Bildirim yok.</p>
                            <?php else: ?>
                            <?php foreach ($notifData['notifications'] as $n): ?>
                            <a href="<?= !empty($n['link']) ? htmlspecialchars($baseUrl . $n['link']) : '#' ?>"
                               class="block border-b border-stone-200 px-4 py-3 text-left transition-colors last:border-b-0 <?= !empty($n['is_read']) ? 'bg-[#FAFAF9]' : 'bg-stone-50' ?> hover:bg-stone-100">
                                <p class="text-sm font-semibold text-stone-800"><?= htmlspecialchars($n['title'] ?? '') ?></p>
                                <?php if (!empty($n['message'])): ?>
                                <p class="mt-0.5 text-xs text-stone-500 line-clamp-2"><?= htmlspecialchars($n['message']) ?></p>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="border-t border-stone-200 px-4 py-2">
                            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/notifications"
                               class="block rounded-md bg-stone-100 py-2 text-center text-sm font-medium text-stone-700 hover:bg-stone-200">
                                Tüm Bildirimleri Gör
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm font-medium text-stone-700">
                    <span class="truncate"><?= htmlspecialchars($adminName) ?></span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 bg-stone-100 p-8">
            <?php if (isset($content)) echo $content; ?>
        </main>
    </div>

    <!-- Toast Notification System -->
    <div x-data="{
        show: false,
        message: '',
        type: 'success',
        _timer: null,
        open(e) {
            this.message = e.detail.message || '';
            this.type = e.detail.type || 'success';
            this.show = true;
            clearTimeout(this._timer);
            this._timer = setTimeout(() => { this.show = false; }, 4000);
        }
    }" @notify.window="open($event)" class="fixed bottom-6 right-6 z-[100]" aria-live="polite">
        <div x-show="show"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-250"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full"
             class="flex items-center gap-3 rounded-lg border shadow-lg min-w-[280px] max-w-[400px] px-4 py-3"
             :class="{
                 'bg-emerald-50 border-emerald-200 text-emerald-800': type === 'success',
                 'bg-rose-50 border-rose-200 text-rose-800': type === 'error',
                 'bg-amber-50 border-amber-200 text-amber-800': type === 'warning',
                 'bg-stone-50 border-stone-200 text-stone-800': type === 'info'
             }">
            <span class="flex-shrink-0" aria-hidden="true">
                <template x-if="type === 'success'">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </template>
                <template x-if="type === 'warning'">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </template>
                <template x-if="type === 'info'">
                    <svg class="h-5 w-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
            </span>
            <p x-text="message" class="text-sm font-medium flex-1"></p>
            <button @click="show = false" class="flex-shrink-0 text-stone-400 hover:text-stone-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <?php
    // Session mesajlarını toast'a çevir
    if (!empty($_SESSION['success'])) {
        $toastMsg = $_SESSION['success'];
        unset($_SESSION['success']);
    ?>
    <script>
    document.addEventListener('alpine:initialized', function() {
        window.dispatchEvent(new CustomEvent('notify', { 
            detail: { message: <?= json_encode($toastMsg, JSON_UNESCAPED_UNICODE) ?>, type: 'success' } 
        }));
    });
    </script>
    <?php } ?>
    <?php if (!empty($_SESSION['error'])) {
        $toastMsg = $_SESSION['error'];
        unset($_SESSION['error']);
    ?>
    <script>
    document.addEventListener('alpine:initialized', function() {
        window.dispatchEvent(new CustomEvent('notify', { 
            detail: { message: <?= json_encode($toastMsg, JSON_UNESCAPED_UNICODE) ?>, type: 'error' } 
        }));
    });
    </script>
    <?php } ?>
</body>
</html>

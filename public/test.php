<?php
/**
 * includes/layout.php ile tasarım test sayfası.
 * Bu dosya public/ içinde olduğu için MAMP document root = public iken
 * /assets/images/... doğru yüklenir (logo 404 olmaz).
 */
session_start();
$baseUrl = ''; // Document root = public ise boş bırakın
$title = 'Lumina Boutique - Tasarım Test';
$content = '<div class="py-20 text-center">
    <h1 class="text-6xl font-display mb-4">Deneme Başlığı</h1>
    <p class="text-secondary">Lüks tasarım sistemi çalışıyor.</p>
    <button class="mt-8 bg-black text-white px-8 py-3 tracking-widest text-xs">ALIŞVERİŞE BAŞLA</button>
</div>';
include __DIR__ . '/../includes/layout.php';

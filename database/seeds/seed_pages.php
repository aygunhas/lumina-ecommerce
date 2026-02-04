<?php

declare(strict_types=1);

/**
 * Sabit sayfalar için örnek kayıtlar (Grup 5).
 * Çalıştırma: proje kökünden -> php database/seeds/seed_pages.php
 */

$configPath = dirname(__DIR__, 2) . '/config/bootstrap.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "Bootstrap bulunamadı: {$configPath}\n");
    exit(1);
}

require $configPath;

use App\Config\Database;

$pdo = Database::getConnection();

$pages = [
    [
        'slug' => 'hakkimizda',
        'title' => 'Hakkımızda',
        'content' => "Lumina Boutique, kadın giyim ve aksesuar alanında kaliteli ürünler sunan bir e-ticaret mağazasıdır.\n\nMüşteri memnuniyetini ön planda tutarak, güvenilir alışveriş deneyimi sunmayı hedefliyoruz.\n\nİletişim sayfamızdan bize ulaşabilirsiniz.",
        'sort_order' => 10,
    ],
    [
        'slug' => 'sss',
        'title' => 'Sıkça Sorulan Sorular',
        'content' => "Sıkça sorulan sorular ve cevapları bu sayfada yer alacaktır.\n\n• Kargo süresi ne kadar?\n• İade nasıl yapılır?\n• Ödeme seçenekleri nelerdir?\n\nBu içerik panelden düzenlenebilir.",
        'sort_order' => 20,
    ],
    [
        'slug' => 'iade-kosullari',
        'title' => 'İade ve Değişim Koşulları',
        'content' => "İade ve değişim koşulları bu sayfada yer alacaktır.\n\n• 14 gün içinde iade hakkı\n• Ürünün kullanılmamış ve etiketli olması gerekliliği\n• İade kargo ücreti\n\nBu metin panelden güncellenebilir.",
        'sort_order' => 30,
    ],
    [
        'slug' => 'kvkk',
        'title' => 'Gizlilik ve KVKK',
        'content' => "Gizlilik politikası ve KVKK aydınlatma metni bu sayfada yer alacaktır.\n\nKişisel verilerinizin işlenmesi, saklanması ve paylaşımına ilişkin bilgiler panelden düzenlenebilir.",
        'sort_order' => 40,
    ],
    [
        'slug' => 'mesafeli-satis-sozlesmesi',
        'title' => 'Mesafeli Satış Sözleşmesi',
        'content' => "Mesafeli satış sözleşmesi metni bu sayfada yer alacaktır.\n\nTaraflar, ön bilgilendirme, cayma hakkı ve diğer yasal metinler panelden düzenlenebilir.",
        'sort_order' => 50,
    ],
];

foreach ($pages as $p) {
    $stmt = $pdo->prepare('SELECT id FROM pages WHERE slug = ? LIMIT 1');
    $stmt->execute([$p['slug']]);
    if ($stmt->fetch()) {
        echo "Sayfa zaten var: {$p['slug']}\n";
        continue;
    }
    $pdo->prepare('
        INSERT INTO pages (slug, title, content, is_active, sort_order, created_at, updated_at)
        VALUES (?, ?, ?, 1, ?, NOW(), NOW())
    ')->execute([$p['slug'], $p['title'], $p['content'], $p['sort_order']]);
    echo "Sayfa eklendi: {$p['slug']} - {$p['title']}\n";
}

echo "Seed tamamlandı.\n";

<?php

declare(strict_types=1);

/**
 * Beden ve Renk özellikleri için örnek veri (varyant altyapısı).
 * Çalıştırma: php database/seeds/seed_attributes.php
 */

$configPath = dirname(__DIR__, 2) . '/config/bootstrap.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "Bootstrap bulunamadı: {$configPath}\n");
    exit(1);
}

require $configPath;

use App\Config\Database;

$pdo = Database::getConnection();

// 1) Beden (size)
$stmt = $pdo->query("SELECT id FROM attributes WHERE slug = 'beden' LIMIT 1");
$bedenId = $stmt->fetchColumn();
if (!$bedenId) {
    $pdo->exec("INSERT INTO attributes (type, name, slug, sort_order) VALUES ('size', 'Beden', 'beden', 10)");
    $bedenId = (int) $pdo->lastInsertId();
    echo "Beden özelliği eklendi (id: {$bedenId}).\n";
}
$bedenValues = [
    ['XXS', null], 
    ['XS', null], 
    ['S', null], 
    ['M', null], 
    ['L', null], 
    ['XL', null], 
    ['XXL', null], 
    ['XXXL', null],
    ['36', null],
    ['38', null],
    ['40', null],
    ['42', null],
    ['44', null],
    ['46', null],
    ['48', null],
    ['50', null],
    ['52', null],
];
foreach ($bedenValues as $i => $v) {
    $stmt = $pdo->prepare('SELECT id FROM attribute_values WHERE attribute_id = ? AND value = ? LIMIT 1');
    $stmt->execute([$bedenId, $v[0]]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare('INSERT INTO attribute_values (attribute_id, value, color_hex, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([$bedenId, $v[0], $v[1], $i * 10]);
        echo "  Beden değeri: {$v[0]}\n";
    }
}

// 2) Renk (color)
$stmt = $pdo->query("SELECT id FROM attributes WHERE slug = 'renk' LIMIT 1");
$renkId = $stmt->fetchColumn();
if (!$renkId) {
    $pdo->exec("INSERT INTO attributes (type, name, slug, sort_order) VALUES ('color', 'Renk', 'renk', 20)");
    $renkId = (int) $pdo->lastInsertId();
    echo "Renk özelliği eklendi (id: {$renkId}).\n";
}
$renkValues = [
    ['Kırmızı', '#c62828'],
    ['Mavi', '#1565c0'],
    ['Siyah', '#212121'],
    ['Beyaz', '#fafafa'],
    ['Gri', '#757575'],
    ['Lacivert', '#0d47a1'],
    ['Yeşil', '#2e7d32'],
    ['Sarı', '#f9a825'],
    ['Turuncu', '#ef6c00'],
    ['Pembe', '#c2185b'],
    ['Mor', '#6a1b9a'],
    ['Kahverengi', '#5d4037'],
    ['Bej', '#d7ccc8'],
    ['Krem', '#fff9e6'],
    ['Bordo', '#880e4f'],
    ['Turkuaz', '#00897b'],
    ['Lavanta', '#9575cd'],
    ['Füme', '#78909c'],
    ['Haki', '#827717'],
    ['Navy', '#0d47a1'],
    ['Koyu Gri', '#424242'],
    ['Açık Gri', '#bdbdbd'],
    ['Altın', '#ffa000'],
    ['Gümüş', '#9e9e9e'],
];
foreach ($renkValues as $i => $v) {
    $stmt = $pdo->prepare('SELECT id FROM attribute_values WHERE attribute_id = ? AND value = ? LIMIT 1');
    $stmt->execute([$renkId, $v[0]]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare('INSERT INTO attribute_values (attribute_id, value, color_hex, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([$renkId, $v[0], $v[1], $i * 10]);
        echo "  Renk değeri: {$v[0]} ({$v[1]})\n";
    }
}

echo "Seed tamamlandı. Panelden Admin → Özellikler (Beden/Renk) ile yönetebilirsiniz.\n";

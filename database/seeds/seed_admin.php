<?php

declare(strict_types=1);

/**
 * İlk admin rolü ve admin kullanıcısı oluşturur.
 * Çalıştırma: proje kökünden -> php database/seeds/seed_admin.php
 */

$configPath = dirname(__DIR__, 2) . '/config/bootstrap.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "Bootstrap bulunamadı: {$configPath}\n");
    exit(1);
}

require $configPath;

use App\Config\Database;

$pdo = Database::getConnection();

// Varsayılan e-posta ve şifre (ilk girişten sonra panelden değiştirilebilir)
$adminEmail = 'admin@luminaboutique.com';
$adminPassword = 'Admin123!';
$adminName = 'Lumina Admin';

// 1) Admin rolü (yoksa ekle)
$stmt = $pdo->query("SELECT id FROM admin_roles WHERE slug = 'super-admin' LIMIT 1");
$roleId = $stmt->fetchColumn();
if (!$roleId) {
    $pdo->exec("INSERT INTO admin_roles (name, slug, description, created_at, updated_at) VALUES ('Süper Admin', 'super-admin', 'Tüm yetkiler', NOW(), NOW())");
    $roleId = (int) $pdo->lastInsertId();
    echo "Admin rolü eklendi (id: {$roleId}).\n";
}

// 2) Admin kullanıcı (yoksa ekle)
$stmt = $pdo->prepare('SELECT id FROM admin_users WHERE email = ? LIMIT 1');
$stmt->execute([$adminEmail]);
$existing = $stmt->fetchColumn();
if ($existing) {
    echo "Bu e-posta zaten kayıtlı: {$adminEmail}. Seed atlandı.\n";
    exit(0);
}

$hash = password_hash($adminPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO admin_users (role_id, email, password, name, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())');
$stmt->execute([$roleId, $adminEmail, $hash, $adminName]);
echo "Admin kullanıcı oluşturuldu.\n";
echo "  E-posta: {$adminEmail}\n";
echo "  Şifre:   {$adminPassword}\n";
echo "İlk girişten sonra şifrenizi panelden değiştirin.\n";

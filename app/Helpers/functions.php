<?php

declare(strict_types=1);

/**
 * Admin ve genel yardımcı fonksiyonlar
 */

/**
 * Admin bildirimlerini veritabanından çeker.
 * Son 5 bildirimi ve okunmamış bildirim sayısını döndürür.
 * Tablo yoksa veya hata olursa boş dizi döner (uygulama çökmez).
 *
 * @return array{notifications: array, unread_count: int}
 */
function get_admin_notifications(): array
{
    try {
        $pdo = \App\Config\Database::getConnection();

        $notifications = $pdo->query("
            SELECT id, title, message, link, is_read, created_at
            FROM admin_notifications
            ORDER BY created_at DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        $unreadCount = (int) $pdo->query("
            SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0
        ")->fetchColumn();

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ];
    } catch (\PDOException $e) {
        return [
            'notifications' => [],
            'unread_count' => 0,
        ];
    }
}

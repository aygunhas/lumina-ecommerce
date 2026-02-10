<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Page Model - Sayfa işlemleri için Model sınıfı
 */
class Page extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'pages';
    }

    /**
     * Slug ile aktif sayfa bulur
     * 
     * @param string $slug Sayfa slug'ı
     * @return array|null Bulunan sayfa veya null
     */
    public static function findActiveBySlug(string $slug): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT id, slug, title, content, meta_title, meta_description 
            FROM pages 
            WHERE slug = ? AND is_active = 1 
            LIMIT 1
        ');
        $stmt->execute([$slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}

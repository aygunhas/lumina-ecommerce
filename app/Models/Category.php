<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Category Model - Kategori işlemleri için Model sınıfı
 */
class Category extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'categories';
    }

    /**
     * Slug ile aktif kategori bulur
     * 
     * @param string $slug Kategori slug'ı
     * @return array|null Bulunan kategori veya null
     */
    public static function findActiveBySlug(string $slug): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT id, name, slug, description, parent_id 
            FROM categories 
            WHERE slug = ? AND is_active = 1 
            LIMIT 1
        ');
        $stmt->execute([$slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Ana kategorileri getirir (parent_id IS NULL)
     * 
     * @param int $limit Limit
     * @return array Kategoriler
     */
    public static function getMainCategories(int $limit = 5): array
    {
        $pdo = self::getConnection();
        $limit = (int) $limit; // Güvenlik için integer'a çevir
        $stmt = $pdo->prepare('
            SELECT id, name, slug, image, home_hero_text 
            FROM categories 
            WHERE parent_id IS NULL AND is_active = 1 
            ORDER BY sort_order ASC, name ASC 
            LIMIT ' . $limit . '
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tüm aktif kategorileri getirir
     * 
     * @return array Kategoriler
     */
    public static function getAllActive(): array
    {
        return self::all(['is_active' => 1], 'sort_order ASC, name ASC');
    }

    /**
     * Bir kategorinin tüm alt kategorilerinin ID'lerini getirir (recursive)
     * 
     * @param int $categoryId Ana kategori ID'si
     * @return array Alt kategori ID'leri (ana kategori ID'si dahil)
     */
    public static function getChildCategoryIds(int $categoryId): array
    {
        $pdo = self::getConnection();
        $categoryIds = [(int)$categoryId]; // Ana kategoriyi de dahil et
        
        // Recursive olarak tüm alt kategorileri bul
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE parent_id = ? AND is_active = 1');
        $stmt->execute([$categoryId]);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($children as $childId) {
            $categoryIds[] = (int)$childId;
            // Recursive olarak alt kategorilerin alt kategorilerini de bul
            $grandChildren = self::getChildCategoryIds((int)$childId);
            $categoryIds = array_merge($categoryIds, $grandChildren);
        }
        
        return array_unique($categoryIds);
    }
}

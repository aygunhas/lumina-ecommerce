<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Product Model - Ürün işlemleri için Model sınıfı
 */
class Product extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'products';
    }

    /**
     * Slug ile aktif ürün bulur
     * 
     * @param string $slug Ürün slug'ı
     * @return array|null Bulunan ürün veya null
     */
    public static function findActiveBySlug(string $slug): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.is_active = 1 
            LIMIT 1
        ');
        $stmt->execute([$slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Ürün görsellerini getirir
     * 
     * @param int $productId Ürün ID'si
     * @return array Görsel path'leri
     */
    public static function getImages(int $productId): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT path 
            FROM product_images 
            WHERE product_id = ? AND attribute_value_id IS NULL
            ORDER BY is_main DESC, sort_order ASC, id ASC
        ');
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Birden fazla ürün için ana görselleri getirir (her ürün için ana görsel veya ilk görsel)
     * 
     * @param array $productIds Ürün ID'leri
     * @return array ['product_id' => 'image_path'] formatında
     */
    public static function getMainImagesForProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $pdo = self::getConnection();
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $pdo->prepare("
            SELECT product_id, path 
            FROM product_images 
            WHERE product_id IN ($placeholders) 
            ORDER BY is_main DESC, sort_order ASC, id ASC
        ");
        $stmt->execute($productIds);

        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($images[$row['product_id']])) {
                $images[$row['product_id']] = $row['path'];
            }
        }

        return $images;
    }

    /**
     * Birden fazla ürün için renk bazlı görselleri getirir (her ürün için her renk için ilk görsel)
     * 
     * @param array $productIds Ürün ID'leri
     * @return array ['product_id' => ['color_id' => ['path1', 'path2', ...]]] formatında
     */
    public static function getColorImagesForProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $pdo = self::getConnection();
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $pdo->prepare("
            SELECT product_id, attribute_value_id, path 
            FROM product_images 
            WHERE product_id IN ($placeholders) AND attribute_value_id IS NOT NULL
            ORDER BY product_id ASC, attribute_value_id ASC, sort_order ASC, id ASC
        ");
        $stmt->execute($productIds);

        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $productId = (int)$row['product_id'];
            $colorId = (int)$row['attribute_value_id'];
            if (!isset($images[$productId])) {
                $images[$productId] = [];
            }
            if (!isset($images[$productId][$colorId])) {
                $images[$productId][$colorId] = [];
            }
            $images[$productId][$colorId][] = $row['path'];
        }

        return $images;
    }

    /**
     * Kategoriye göre ürünleri getirir
     * 
     * @param int $categoryId Kategori ID'si
     * @param array $options Seçenekler (orderBy, limit, offset)
     * @return array Ürünler
     */
    public static function getByCategory(int $categoryId, array $options = []): array
    {
        return self::getByCategories([$categoryId], $options);
    }

    /**
     * Birden fazla kategoriye göre ürünleri getirir
     * 
     * @param array $categoryIds Kategori ID'leri
     * @param array $options Seçenekler (orderBy, limit, offset)
     * @return array Ürünler
     */
    public static function getByCategories(array $categoryIds, array $options = []): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        
        $pdo = self::getConnection();
        $orderBy = $options['orderBy'] ?? 'p.sort_order ASC, p.name ASC';
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;

        // ORDER BY için güvenlik kontrolü - sadece izin verilen karakterler
        $orderBy = preg_replace('/[^a-zA-Z0-9_.,\s]/', '', $orderBy);
        
        // Kategori ID'lerini integer'a çevir ve güvenlik için placeholders oluştur
        $categoryIds = array_map('intval', $categoryIds);
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        
        $sql = "
            SELECT 
                p.id, 
                p.name, 
                p.slug, 
                COALESCE(MIN(pv.price), p.price, 0) as price,
                COALESCE(MIN(CASE WHEN pv.sale_price IS NOT NULL AND pv.sale_price > 0 THEN pv.sale_price END), p.sale_price) as sale_price,
                p.short_description, 
                p.is_featured, 
                p.is_new
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE p.category_id IN ($placeholders) AND p.is_active = 1
            GROUP BY p.id, p.name, p.slug, p.price, p.sale_price, p.short_description, p.is_featured, p.is_new
            ORDER BY $orderBy
        ";

        if ($limit !== null) {
            $limit = (int) $limit;
            $offset = (int) $offset;
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($categoryIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Arama yapar
     * 
     * @param string $query Arama terimi
     * @param array $options Seçenekler (orderBy, limit, offset)
     * @return array Ürünler
     */
    public static function search(string $query, array $options = []): array
    {
        $pdo = self::getConnection();
        $term = '%' . $query . '%';
        $orderBy = $options['orderBy'] ?? 'p.created_at DESC';
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;

        // ORDER BY için güvenlik kontrolü
        $orderBy = preg_replace('/[^a-zA-Z0-9_.,\s]/', '', $orderBy);

        $sql = "
            SELECT 
                p.id, 
                p.name, 
                p.slug, 
                COALESCE(MIN(pv.price), p.price, 0) as price,
                COALESCE(MIN(CASE WHEN pv.sale_price IS NOT NULL AND pv.sale_price > 0 THEN pv.sale_price END), p.sale_price) as sale_price,
                p.short_description, 
                p.is_featured, 
                p.is_new
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE p.is_active = 1 AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?)
            GROUP BY p.id, p.name, p.slug, p.price, p.sale_price, p.short_description, p.is_featured, p.is_new
            ORDER BY $orderBy
        ";

        if ($limit !== null) {
            $limit = (int) $limit;
            $offset = (int) $offset;
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Öne çıkan ürünleri getirir
     * 
     * @param int $limit Limit
     * @return array Ürünler
     */
    public static function getFeatured(int $limit = 8): array
    {
        $pdo = self::getConnection();
        $limit = (int) $limit; // Güvenlik için integer'a çevir
        $stmt = $pdo->prepare('
            SELECT 
                p.id, 
                p.name, 
                p.slug, 
                COALESCE(MIN(pv.price), p.price, 0) as price,
                COALESCE(MIN(CASE WHEN pv.sale_price IS NOT NULL AND pv.sale_price > 0 THEN pv.sale_price END), p.sale_price) as sale_price,
                p.is_featured, 
                p.is_new 
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE p.is_active = 1 AND p.is_featured = 1 
            GROUP BY p.id, p.name, p.slug, p.price, p.sale_price, p.is_featured, p.is_new
            ORDER BY p.sort_order ASC, p.name ASC 
            LIMIT ' . $limit . '
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Benzer ürünleri getirir (aynı kategoriden)
     * 
     * @param int $productId Ürün ID'si
     * @param int|null $categoryId Kategori ID'si
     * @param int $limit Limit
     * @return array Ürünler
     */
    public static function getRelated(int $productId, ?int $categoryId = null, int $limit = 4): array
    {
        $pdo = self::getConnection();
        $limit = (int) $limit; // Güvenlik için integer'a çevir

        if ($categoryId !== null && $categoryId > 0) {
            $stmt = $pdo->prepare('
                SELECT 
                    p.id, 
                    p.name, 
                    p.slug, 
                    COALESCE(MIN(pv.price), p.price, 0) as price,
                    COALESCE(MIN(CASE WHEN pv.sale_price IS NOT NULL AND pv.sale_price > 0 THEN pv.sale_price END), p.sale_price) as sale_price,
                    p.is_featured, 
                    p.is_new
                FROM products p
                LEFT JOIN product_variants pv ON p.id = pv.product_id
                WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
                GROUP BY p.id, p.name, p.slug, p.price, p.sale_price, p.is_featured, p.is_new
                ORDER BY RAND()
                LIMIT ' . $limit . '
            ');
            $stmt->execute([$categoryId, $productId]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $products = [];
        }

        // Eğer yeterli ürün yoksa, rastgele ürünler ekle
        if (count($products) < $limit) {
            $stmt = $pdo->prepare('
                SELECT 
                    p.id, 
                    p.name, 
                    p.slug, 
                    COALESCE(MIN(pv.price), p.price, 0) as price,
                    COALESCE(MIN(CASE WHEN pv.sale_price IS NOT NULL AND pv.sale_price > 0 THEN pv.sale_price END), p.sale_price) as sale_price,
                    p.is_featured, 
                    p.is_new
                FROM products p
                LEFT JOIN product_variants pv ON p.id = pv.product_id
                WHERE p.id != ? AND p.is_active = 1
                GROUP BY p.id, p.name, p.slug, p.price, p.sale_price, p.is_featured, p.is_new
                ORDER BY RAND()
                LIMIT ' . $limit . '
            ');
            $stmt->execute([$productId]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $products;
    }

    /**
     * Ürün görüntülenme sayısını artırır
     * 
     * @param int $productId Ürün ID'si
     */
    public static function incrementViewCount(int $productId): void
    {
        $pdo = self::getConnection();
        $pdo->prepare('UPDATE products SET view_count = view_count + 1 WHERE id = ?')->execute([$productId]);
    }

    /**
     * Ürün varyantlarını getirir
     * 
     * @param int $productId Ürün ID'si
     * @return array Varyantlar
     */
    public static function getVariants(int $productId): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT pv.*
            FROM product_variants pv
            WHERE pv.product_id = ?
            ORDER BY pv.id ASC
        ');
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Varyant attribute değerlerini getirir
     * 
     * @param int $variantId Varyant ID'si
     * @return array Attribute değerleri
     */
    public static function getVariantAttributes(int $variantId): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT av.value 
            FROM product_variant_attribute_values pvav
            INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
            WHERE pvav.variant_id = ? 
            ORDER BY av.sort_order
        ');
        $stmt->execute([$variantId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Birden fazla ürün için varyant varlığını kontrol eder
     * 
     * @param array $productIds Ürün ID'leri
     * @return array ['product_id' => true/false] formatında - ürünün varyantı var mı?
     */
    public static function hasVariantsForProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $pdo = self::getConnection();
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $pdo->prepare("
            SELECT DISTINCT product_id 
            FROM product_variants 
            WHERE product_id IN ($placeholders)
        ");
        $stmt->execute($productIds);

        $result = [];
        foreach ($productIds as $id) {
            $result[$id] = false;
        }
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['product_id']] = true;
        }

        return $result;
    }
}

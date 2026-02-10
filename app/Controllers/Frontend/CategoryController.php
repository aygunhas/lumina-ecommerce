<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Models\Category;
use App\Models\Product;
use PDO;

/**
 * Mağaza: Kategori sayfası – kategorideki ürünleri listeler (sıralama, sayfalama)
 */
class CategoryController extends FrontendBaseController
{
    private const PER_PAGE_OPTIONS = [12, 24];
    private const SORT_OPTIONS = [
        'default' => ['p.sort_order', 'ASC', 'p.name', 'ASC'],
        'newest' => ['p.created_at', 'DESC', 'p.id', 'DESC'],
        'price_asc' => ['p.price', 'ASC', 'p.id', 'ASC'],
        'price_desc' => ['p.price', 'DESC', 'p.id', 'DESC'],
        'name_asc' => ['p.name', 'ASC', 'p.id', 'ASC'],
    ];

    public function show(): void
    {
        $slug = $_GET['_slug'] ?? '';
        if ($slug === '') {
            $this->redirect('/');
        }
        
        $category = Category::findActiveBySlug($slug);
        if (!$category) {
            require BASE_PATH . '/includes/render-404.php';
            return;
        }
        
        // Kategorinin ana kategori olup olmadığını kontrol et (parent_id NULL veya 0)
        $isMainCategory = ($category['parent_id'] === null || (int)$category['parent_id'] === 0);
        
        // Ana kategori ise, tüm alt kategorilerin ID'lerini al
        $categoryIds = $isMainCategory 
            ? Category::getChildCategoryIds((int)$category['id'])
            : [(int)$category['id']];
        
        $pdo = Database::getConnection();
        
        $sort = $_GET['sort'] ?? 'default';
        if (!isset(self::SORT_OPTIONS[$sort])) {
            $sort = 'default';
        }
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 12;
        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 12;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        [$orderCol1, $orderDir1, $orderCol2, $orderDir2] = self::SORT_OPTIONS[$sort];

        // Toplam ürün sayısını hesapla (tüm alt kategoriler dahil)
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE p.category_id IN ($placeholders) AND p.is_active = 1");
        $countStmt->execute($categoryIds);
        $totalRows = (int) $countStmt->fetchColumn();
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Ürünleri getir (tüm alt kategoriler dahil)
        $products = Product::getByCategories($categoryIds, [
            'orderBy' => "$orderCol1 $orderDir1, $orderCol2 $orderDir2",
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        $productImages = [];
        $productHasVariants = [];
        $productColorImages = [];
        $productColorVariants = []; // Her ürün için renk varyantları
        if (!empty($products)) {
            $ids = array_column($products, 'id');
            $productImages = Product::getMainImagesForProducts($ids);
            $productHasVariants = Product::hasVariantsForProducts($ids);
            $productColorImages = Product::getColorImagesForProducts($ids);
            
            // Her ürün için renk varyantlarını çek
            $pdo = Database::getConnection();
            foreach ($ids as $productId) {
                // Renk attribute'unu bul
                $colorAttrStmt = $pdo->prepare('SELECT id FROM attributes WHERE type = ? LIMIT 1');
                $colorAttrStmt->execute(['color']);
                $colorAttrId = $colorAttrStmt->fetchColumn();
                
                if ($colorAttrId) {
                    // Bu ürünün varyantlarındaki renkleri çek
                    $variantStmt = $pdo->prepare('
                        SELECT DISTINCT av.id, av.value, av.color_hex, av.sort_order
                        FROM product_variants pv
                        INNER JOIN product_variant_attribute_values pvav ON pv.id = pvav.variant_id
                        INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                        WHERE pv.product_id = ? AND av.attribute_id = ?
                        ORDER BY av.sort_order ASC
                    ');
                    $variantStmt->execute([$productId, $colorAttrId]);
                    $productColorVariants[$productId] = $variantStmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $productColorVariants[$productId] = [];
                }
            }
        }
        $title = $category['name'] . ' - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/category/show', compact('title', 'baseUrl', 'category', 'products', 'productImages', 'productHasVariants', 'productColorImages', 'productColorVariants', 'sort', 'perPage', 'page', 'totalPages', 'totalRows'));
    }
}

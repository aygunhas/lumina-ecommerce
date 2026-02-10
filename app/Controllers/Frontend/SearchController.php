<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Models\Product;
use PDO;

/**
 * Mağaza: Ürün arama – kelime ile arama, sıralama, sayfalama; header için canlı öneri API
 */
class SearchController extends FrontendBaseController
{
    private const PER_PAGE_OPTIONS = [12, 24];
    private const SUGGEST_LIMIT = 5;
    private const SORT_OPTIONS = [
        'newest' => ['created_at', 'DESC'],
        'price_asc' => ['price', 'ASC'],
        'price_desc' => ['price', 'DESC'],
        'name_asc' => ['name', 'ASC'],
    ];

    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            $this->redirect('/');
        }
        $baseUrl = $this->baseUrl();
        $sort = $_GET['sort'] ?? 'newest';
        if (!isset(self::SORT_OPTIONS[$sort])) {
            $sort = 'newest';
        }
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 12;
        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 12;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        [$orderCol, $orderDir] = self::SORT_OPTIONS[$sort];
        $orderBy = $orderCol === 'created_at' ? 'p.created_at ' . $orderDir : 'p.' . $orderCol . ' ' . $orderDir . ', p.id ASC';
        
        // Toplam kayıt sayısı için ayrı sorgu
        $pdo = Database::getConnection();
        $term = '%' . $q . '%';
        $countSql = "SELECT COUNT(*) FROM products p WHERE p.is_active = 1 AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?)";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute([$term, $term, $term]);
        $totalRows = (int) $stmt->fetchColumn();
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Product Model kullanarak arama yap
        $products = Product::search($q, [
            'orderBy' => $orderBy,
            'limit' => $perPage,
            'offset' => $offset
        ]);

        $productImages = [];
        $productHasVariants = [];
        $productColorImages = [];
        $productColorVariants = [];
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

        $title = 'Arama: ' . $q . ' - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/search/index', compact('title', 'baseUrl', 'q', 'products', 'productImages', 'productHasVariants', 'productColorImages', 'productColorVariants', 'sort', 'perPage', 'page', 'totalPages', 'totalRows'));
    }

    /**
     * Header canlı arama için JSON öneri: ürünler + kategoriler (GET ?q=)
     */
    public function suggest(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) {
            $this->json(['products' => [], 'categories' => []]);
        }

        // Product Model kullanarak arama yap
        $products = Product::search($q, [
            'orderBy' => 'p.name ASC',
            'limit' => self::SUGGEST_LIMIT
        ]);

        // Ürün görsellerini ekle
        $productImages = [];
        if (!empty($products)) {
            $ids = array_column($products, 'id');
            $productImages = Product::getMainImagesForProducts($ids);
        }
        foreach ($products as &$p) {
            $p['image'] = $productImages[$p['id']] ?? null;
        }
        unset($p);

        // Kategoriler için hala PDO kullanıyoruz (Category Model'e search metodu eklenebilir)
        $pdo = Database::getConnection();
        $term = '%' . $q . '%';
        $stmt = $pdo->prepare("SELECT id, name, slug FROM categories WHERE is_active = 1 AND name LIKE ? ORDER BY name ASC LIMIT " . self::SUGGEST_LIMIT);
        $stmt->execute([$term]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json(['products' => $products, 'categories' => $categories]);
    }
}

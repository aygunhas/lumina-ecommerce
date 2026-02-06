<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use PDO;

/**
 * Mağaza: Ürün arama – kelime ile arama, sıralama, sayfalama; header için canlı öneri API
 */
class SearchController
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
        $baseUrl = $this->baseUrl();
        if ($q === '') {
            header('Location: ' . $baseUrl . '/');
            exit;
        }
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

        $pdo = Database::getConnection();
        [$orderCol, $orderDir] = self::SORT_OPTIONS[$sort];
        $safeCol = $orderCol === 'created_at' ? 'p.created_at' : 'p.' . $orderCol;

        $term = '%' . $q . '%';
        $countSql = "SELECT COUNT(*) FROM products p WHERE p.is_active = 1 AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?)";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute([$term, $term, $term]);
        $totalRows = (int) $stmt->fetchColumn();
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.short_description, p.is_featured, p.is_new
                FROM products p
                WHERE p.is_active = 1 AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?)
                ORDER BY $safeCol $orderDir, p.id ASC
                LIMIT $perPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$term, $term, $term]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $productImages = [];
        if (!empty($products)) {
            $ids = array_column($products, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT product_id, path FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
            $stmt->execute($ids);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($productImages[$row['product_id']])) {
                    $productImages[$row['product_id']] = $row['path'];
                }
            }
        }

        $title = 'Arama: ' . $q . ' - ' . env('APP_NAME', 'Lumina Boutique');
        $this->render('frontend/search/index', compact('title', 'baseUrl', 'q', 'products', 'productImages', 'sort', 'perPage', 'page', 'totalPages', 'totalRows'));
    }

    /**
     * Header canlı arama için JSON öneri: ürünler + kategoriler (GET ?q=)
     */
    public function suggest(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) {
            echo json_encode(['products' => [], 'categories' => []]);
            return;
        }
        $pdo = Database::getConnection();
        $term = '%' . $q . '%';

        $products = [];
        $stmt = $pdo->prepare("SELECT p.id, p.name, p.slug, p.price, p.sale_price FROM products p WHERE p.is_active = 1 AND (p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?) ORDER BY p.name ASC LIMIT " . self::SUGGEST_LIMIT);
        $stmt->execute([$term, $term, $term]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $productImages = [];
        if (!empty($products)) {
            $ids = array_column($products, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT product_id, path FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
            $stmt->execute($ids);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($productImages[$row['product_id']])) {
                    $productImages[$row['product_id']] = $row['path'];
                }
            }
        }
        foreach ($products as &$p) {
            $p['image'] = $productImages[$p['id']] ?? null;
        }
        unset($p);

        $categories = [];
        $stmt = $pdo->prepare("SELECT id, name, slug FROM categories WHERE is_active = 1 AND name LIKE ? ORDER BY name ASC LIMIT " . self::SUGGEST_LIMIT);
        $stmt->execute([$term]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['products' => $products, 'categories' => $categories], JSON_UNESCAPED_UNICODE);
    }

    private function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            echo '<p>Görünüm bulunamadı.</p>';
            return;
        }
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        $layoutPath = BASE_PATH . '/app/Views/frontend/layouts/main.php';
        require $layoutPath;
    }
}

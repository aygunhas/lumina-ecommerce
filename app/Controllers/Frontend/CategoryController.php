<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use PDO;

/**
 * Mağaza: Kategori sayfası – kategorideki ürünleri listeler (sıralama, sayfalama)
 */
class CategoryController
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
            header('Location: ' . $this->baseUrl() . '/');
            exit;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, name, slug, description FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$category) {
            http_response_code(404);
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404</title></head><body><h1>Kategori bulunamadı</h1></body></html>';
            exit;
        }
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

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE p.category_id = ? AND p.is_active = 1");
        $stmt->execute([$category['id']]);
        $totalRows = (int) $stmt->fetchColumn();
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.short_description, p.is_featured, p.is_new
            FROM products p
            WHERE p.category_id = ? AND p.is_active = 1
            ORDER BY $orderCol1 $orderDir1, $orderCol2 $orderDir2
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute([$category['id']]);
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
        $title = $category['name'] . ' - ' . env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/category/show', compact('title', 'baseUrl', 'category', 'products', 'productImages', 'sort', 'perPage', 'page', 'totalPages', 'totalRows'));
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

<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use PDO;

/**
 * Mağaza anasayfa – kategoriler ve öne çıkan ürünler
 */
class HomeController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $categories = $pdo->query('
            SELECT id, name, slug FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order ASC, name ASC
        ')->fetchAll(PDO::FETCH_ASSOC);
        $featuredProducts = $pdo->query('
            SELECT id, name, slug, price, sale_price, is_featured, is_new FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC, name ASC LIMIT 8
        ')->fetchAll(PDO::FETCH_ASSOC);
        // Öne çıkan azsa veya yoksa: son eklenen ürünlerle listeyi 8'e tamamla (çakışan id'leri çıkarıp ekle)
        $limit = 8;
        if (count($featuredProducts) < $limit) {
            $excludeIds = array_column($featuredProducts, 'id');
            $placeholders = $excludeIds ? implode(',', array_fill(0, count($excludeIds), '?')) : '';
            $extraSql = $placeholders
                ? "SELECT id, name, slug, price, sale_price, is_featured, is_new FROM products WHERE is_active = 1 AND id NOT IN ($placeholders) ORDER BY created_at DESC LIMIT " . ($limit - count($featuredProducts))
                : "SELECT id, name, slug, price, sale_price, is_featured, is_new FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT $limit";
            $stmt = $placeholders ? $pdo->prepare($extraSql) : $pdo->query($extraSql);
            if ($placeholders) {
                $stmt->execute($excludeIds);
            }
            $extra = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $featuredProducts = array_merge($featuredProducts, $extra);
        }
        if (empty($featuredProducts)) {
            $featuredProducts = $pdo->query('
                SELECT id, name, slug, price, sale_price, is_featured, is_new FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8
            ')->fetchAll(PDO::FETCH_ASSOC);
        }
        $productImages = [];
        if (!empty($featuredProducts)) {
            $ids = array_column($featuredProducts, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT product_id, path FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
            $stmt->execute($ids);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($productImages[$row['product_id']])) {
                    $productImages[$row['product_id']] = $row['path'];
                }
            }
        }

        $sliders = $pdo->query('
            SELECT id, title, subtitle, image, link, link_text
            FROM sliders
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ')->fetchAll(PDO::FETCH_ASSOC);

        $title = env('APP_NAME', 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->renderWithIncludesLayout('frontend/home', compact('title', 'baseUrl', 'categories', 'featuredProducts', 'productImages', 'sliders'));
    }

    private function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }

    /** includes/layout.php kullanır (top bar, header, footer, cart-drawer, toast). */
    private function renderWithIncludesLayout(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            echo '<p>Görünüm bulunamadı: ' . htmlspecialchars($view) . '</p>';
            return;
        }
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require BASE_PATH . '/includes/layout.php';
    }
}

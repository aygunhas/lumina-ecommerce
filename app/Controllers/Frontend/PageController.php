<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use PDO;

/**
 * Mağaza: Sabit sayfalar (Hakkımızda vb.) ve /sayfa/:slug ile sayfa gösterme
 */
class PageController
{
    public function about(): void
    {
        $title = 'Hakkımızda - ' . (function_exists('env') ? env('APP_NAME', 'Lumina Boutique') : 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/pages/about', compact('title', 'baseUrl'));
    }

    /**
     * Sipariş takibi (üye olmayan kullanıcılar için) – includes/layout.php kullanır.
     * GET ?q= ile sipariş no veya e-posta; veritabanından sipariş, kalemler ve kargolar çekilir.
     */
    public function trackOrder(): void
    {
        $title = 'Sipariş Takibi - ' . (function_exists('env') ? env('APP_NAME', 'Lumina Boutique') : 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $q = trim((string) ($_GET['q'] ?? ''));
        $order = null;
        $items = [];
        $shipments = [];
        $notFound = false;

        if ($q !== '') {
            $pdo = Database::getConnection();
            if (strpos($q, '@') !== false) {
                $stmt = $pdo->prepare('SELECT * FROM orders WHERE LOWER(guest_email) = LOWER(?) ORDER BY created_at DESC LIMIT 1');
                $stmt->execute([$q]);
            } else {
                $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
                $stmt->execute([$q]);
            }
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($order) {
                $orderId = (int) $order['id'];
                $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
                $stmt->execute([$orderId]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ? ORDER BY id ASC');
                $stmt->execute([$orderId]);
                $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $notFound = true;
            }
        }

        $this->renderWithIncludesLayout('frontend/track-order', compact('title', 'baseUrl', 'order', 'items', 'shipments', 'q', 'notFound'));
    }

    /**
     * Yardım Merkezi / SSS (Sıkça Sorulan Sorular)
     */
    public function faq(): void
    {
        $title = 'Yardım Merkezi - ' . (function_exists('env') ? env('APP_NAME', 'Lumina Boutique') : 'Lumina Boutique');
        $baseUrl = $this->baseUrl();
        $this->render('frontend/pages/faq', compact('title', 'baseUrl'));
    }

    /** Politika sayfalarında kullanılan slug listesi (policy-detail şablonu) */
    private const POLICY_SLUGS = ['gizlilik', 'kvkk', 'iade-kosullari', 'mesafeli-satis-sozlesmesi', 'cerez-politikasi'];

    /**
     * Slug ile sayfa gösterir (pages tablosundan). Bulunamazsa 404.
     * Gizlilik, İade, Mesafeli Satış vb. için policy-detail şablonu kullanılır.
     */
    public function showBySlug(): void
    {
        $slug = $_GET['_slug'] ?? '';
        if ($slug === '') {
            $this->send404();
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, slug, title, content, meta_title, meta_description FROM pages WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$page) {
            $this->send404();
            return;
        }

        $pageTitle = $page['meta_title'] ?: $page['title'];
        $title = $pageTitle . ' - ' . (function_exists('env') ? env('APP_NAME', 'Lumina Boutique') : 'Lumina Boutique');
        $baseUrl = $this->baseUrl();

        if (in_array($slug, self::POLICY_SLUGS, true)) {
            $navItems = $this->extractNavFromHtml($page['content'] ?? '');
            $this->render('frontend/pages/policy-detail', [
                'title' => $title,
                'baseUrl' => $baseUrl,
                'policyTitle' => mb_strtoupper($page['title']),
                'updatedAt' => '',
                'content' => $page['content'],
                'navItems' => $navItems,
            ]);
            return;
        }

        $this->render('frontend/pages/show', [
            'title' => $title,
            'baseUrl' => $baseUrl,
            'page' => $page,
        ]);
    }

    /**
     * HTML içeriğinden h2/h3 id ve metinlerini çıkarır (sidebar navigasyon için).
     */
    private function extractNavFromHtml(string $html): array
    {
        $nav = [];
        if (preg_match_all('/<h[23][^>]*\bid=["\']([^"\']+)["\'][^>]*>([^<]+)</i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $nav[] = ['id' => $match[1], 'label' => trim(strip_tags($match[2]))];
            }
        }
        return $nav;
    }

    private function send404(): void
    {
        require BASE_PATH . '/includes/render-404.php';
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

    /** includes/layout.php kullanır (header, footer, cart-drawer, toast). */
    private function renderWithIncludesLayout(string $view, array $data = []): void
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
        $layoutPath = BASE_PATH . '/includes/layout.php';
        require $layoutPath;
    }
}

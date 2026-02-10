<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;
use App\Models\Order;
use App\Models\Page;
use PDO;

/**
 * Mağaza: Sabit sayfalar (Hakkımızda vb.) ve /sayfa/:slug ile sayfa gösterme
 */
class PageController extends FrontendBaseController
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
            if (strpos($q, '@') !== false) {
                $order = Order::findLatestByGuestEmail($q);
            } else {
                $order = Order::findByOrderNumber($q);
            }
            
            if ($order) {
                $orderId = (int) $order['id'];
                $items = Order::getItems($orderId);
                $shipments = Order::getShipments($orderId);
            } else {
                $notFound = true;
            }
        }

        $this->render('frontend/track-order', compact('title', 'baseUrl', 'order', 'items', 'shipments', 'q', 'notFound'));
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

        $page = Page::findActiveBySlug($slug);

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
}

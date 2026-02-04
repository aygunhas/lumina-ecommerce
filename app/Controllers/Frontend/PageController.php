<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Config\Database;

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
     * Slug ile sayfa gösterir (pages tablosundan). Bulunamazsa 404.
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
        $this->render('frontend/pages/show', [
            'title' => $title,
            'baseUrl' => $baseUrl,
            'page' => $page,
        ]);
    }

    private function send404(): void
    {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404</title></head><body><h1>Sayfa bulunamadı</h1></body></html>';
        exit;
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

<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

/**
 * Frontend controller'lar için ortak taban sınıf
 * Tüm Frontend controller'lar bu sınıfı extend edecek
 * 
 * Bu sınıf şunları sağlar:
 * - baseUrl(): Base URL'i döndürür
 * - render(): View render et (includes/layout.php kullanır)
 * - renderWithoutLayout(): Layout olmadan render et
 * - redirect(): Yönlendirme yapar
 * - json(): JSON response döndürür
 * - userId(): Kullanıcı ID'sini döndürür
 */
abstract class FrontendBaseController
{
    /**
     * Base URL'i döndürür
     * Örnek: Eğer site /lumina-ecommerce/public altındaysa "/lumina-ecommerce/public" döner
     * Eğer root'taysa boş string döner
     */
    protected function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }

    /**
     * View render et (includes/layout.php kullanır)
     * Bu metod artık tüm Frontend sayfaları için standart render metodudur
     * 
     * @param string $viewName View dosyasının yolu (örn: 'frontend/home' veya 'frontend/product/show')
     * @param array $data View'a gönderilecek veriler
     */
    protected function render(string $viewName, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $baseUrl = $baseUrl ?? $this->baseUrl();
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        if (($pos = strpos($currentUri, '?')) !== false) {
            $currentUri = substr($currentUri, 0, $pos);
        }
        $currentUri = rtrim($currentUri, '/') ?: '/';

        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $viewName) . '.php';
        if (!is_file($viewPath)) {
            echo '<p>Görünüm bulunamadı: ' . htmlspecialchars($viewName) . '</p>';
            return;
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $pageTitle = $data['pageTitle'] ?? null;
        $layoutPath = BASE_PATH . '/includes/layout.php';
        require $layoutPath;
    }


    /**
     * Layout olmadan render et (AJAX, JSON response için)
     * 
     * @param string $viewName View dosyasının yolu
     * @param array $data View'a gönderilecek veriler
     */
    protected function renderWithoutLayout(string $viewName, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $baseUrl = $baseUrl ?? $this->baseUrl();
        
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $viewName) . '.php';
        if (!is_file($viewPath)) {
            echo '<p>Görünüm bulunamadı: ' . htmlspecialchars($viewName) . '</p>';
            return;
        }
        
        require $viewPath;
    }

    /**
     * Yönlendirme yapar
     * 
     * @param string $url Yönlendirilecek URL (örn: '/sepet' veya '/urun/test-urunu')
     * @param int $statusCode HTTP durum kodu (varsayılan: 302)
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        $baseUrl = $this->baseUrl();
        $fullUrl = $baseUrl . $url;
        header('Location: ' . $fullUrl, true, $statusCode);
        exit;
    }

    /**
     * JSON response döndürür (AJAX istekleri için)
     * 
     * @param array $data Gönderilecek veri
     * @param int $statusCode HTTP durum kodu (varsayılan: 200)
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * AJAX response döndürür (geriye dönük uyumluluk için)
     * 
     * @param array $data Gönderilecek veri
     */
    protected function ajaxResponse(array $data): void
    {
        $this->json($data);
    }

    /**
     * Kullanıcı ID'sini döndürür (giriş yapmış kullanıcı için)
     * 
     * @return int Kullanıcı ID'si, giriş yapılmamışsa 0
     */
    protected function userId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    /**
     * Admin ID'sini döndürür (frontend'de nadiren gerekir)
     * 
     * @return int Admin ID'si, giriş yapılmamışsa 0
     */
    protected function adminId(): int
    {
        return (int) ($_SESSION['admin_id'] ?? 0);
    }
}

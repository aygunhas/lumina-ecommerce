<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

/**
 * Admin sayfaları için ortak taban: layout ile render, baseUrl
 */
abstract class AdminBaseController
{
    protected function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }

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
        $layoutPath = BASE_PATH . '/app/Views/admin/layouts/main.php';
        require $layoutPath;
    }

    /** Layout kullanmadan sadece view çıktısı (yazdırma sayfaları için) */
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
}

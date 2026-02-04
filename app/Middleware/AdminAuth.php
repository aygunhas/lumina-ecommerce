<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Admin oturum kontrolü: giriş yoksa login sayfasına yönlendirir
 */
class AdminAuth
{
    public function handle(): void
    {
        if (empty($_SESSION['admin_id'] ?? null)) {
            header('Location: ' . $this->baseUrl() . '/admin/login');
            exit;
        }
    }

    private function baseUrl(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        return ($base === '/' || $base === '\\') ? '' : $base;
    }
}

<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Müşteri oturum kontrolü: giriş yoksa /giris sayfasına yönlendirir
 */
class UserAuth
{
    public function handle(): void
    {
        if (empty($_SESSION['user_id'] ?? null)) {
            header('Location: ' . $this->baseUrl() . '/giris?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/hesabim'));
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

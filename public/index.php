<?php

declare(strict_types=1);

/**
 * Lumina Boutique - Tek giriş noktası
 * Tüm istekler bu dosyaya yönlendirilmeli (Apache: RewriteRule -> index.php)
 */

$basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

require dirname(__DIR__) . '/config/bootstrap.php';

use App\Router;
use App\Config\Database;
use App\Middleware\AdminAuth;
use App\Middleware\UserAuth;

$router = new Router($basePath);
$routes = require dirname(__DIR__) . '/config/routes.php';
$matched = $router->match($routes);

if ($matched === null) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404</title></head><body><h1>Sayfa bulunamadı</h1></body></html>';
    exit;
}

[$controllerClass, $method] = $matched;
$middlewares = $matched[2] ?? [];

// Middleware'leri çalıştır
foreach ($middlewares as $m) {
    if ($m === 'admin') {
        (new AdminAuth())->handle();
    }
    if ($m === 'user') {
        (new UserAuth())->handle();
    }
}

// Controller'ı çalıştır
$controller = new $controllerClass();
$controller->$method();

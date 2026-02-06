<?php

declare(strict_types=1);

/**
 * Lumina Boutique - Uygulama başlatıcı
 * .env yükler, sabitleri tanımlar, autoload kaydeder.
 */

// Proje kök dizini (config klasörünün bir üstü)
define('BASE_PATH', dirname(__DIR__));

// Oturum (admin ve müşteri girişi için)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlama (geliştirme ortamında)
if (($_ENV['APP_DEBUG'] ?? '0') === '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// .env dosyasını yükle
$envPath = BASE_PATH . '/.env';
if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// env() yardımcı fonksiyonu
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }
}

// Helper fonksiyonları (get_admin_notifications vb.)
$helpersPath = BASE_PATH . '/app/Helpers/functions.php';
if (is_file($helpersPath)) {
    require $helpersPath;
}

// Basit PSR-4 benzeri autoload: App\ -> app/
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

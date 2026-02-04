<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/**
 * Veritabanı bağlantısı (PDO tek örnek)
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = env('DB_HOST', 'localhost');
        $port = null;
        if (strpos($host, ':') !== false) {
            [$host, $port] = explode(':', $host, 2);
        }
        $dsn = 'mysql:host=' . $host . ($port ? ';port=' . $port : '') . ';dbname=' . env('DB_NAME', 'lumina_db') . ';charset=utf8mb4';
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASSWORD', '');

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new PDOException('Veritabanı bağlantısı kurulamadı: ' . $e->getMessage());
        }

        return self::$pdo;
    }
}

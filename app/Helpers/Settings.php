<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Config\Database;

/**
 * Ayarlar tablosundan okuma/yazma (group_name + key)
 */
class Settings
{
    /** @var array<string, string|null> group_key => value (cache) */
    private static array $cache = [];

    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        $cacheKey = $group . '.' . $key;
        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey];
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT value FROM settings WHERE group_name = ? AND `key` = ? LIMIT 1');
        $stmt->execute([$group, $key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $value = $row ? $row['value'] : $default;
        self::$cache[$cacheKey] = $value;
        return $value;
    }

    public static function set(string $group, string $key, ?string $value): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO settings (group_name, `key`, value, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()');
        $stmt->execute([$group, $key, $value]);
        self::$cache[$group . '.' . $key] = $value;
    }

    /** Tüm ayarları group'a göre dizi olarak getirir */
    public static function getGroup(string $group): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT `key`, value FROM settings WHERE group_name = ?');
        $stmt->execute([$group]);
        $out = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $out[$row['key']] = $row['value'];
            self::$cache[$group . '.' . $row['key']] = $row['value'];
        }
        return $out;
    }
}

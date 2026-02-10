<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Address Model - Adres işlemleri için Model sınıfı
 */
class Address extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'addresses';
    }

    /**
     * Kullanıcıya ait adresleri getirir
     * 
     * @param int $userId Kullanıcı ID'si
     * @return array Adresler
     */
    public static function getByUserId(int $userId): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT id, title, first_name, last_name, phone, city, district, address_line, postal_code, is_default 
            FROM addresses 
            WHERE user_id = ? 
            ORDER BY is_default DESC, id ASC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kullanıcının varsayılan adresini getirir
     * 
     * @param int $userId Kullanıcı ID'si
     * @return array|null Bulunan adres veya null
     */
    public static function getDefaultByUserId(int $userId): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT * 
            FROM addresses 
            WHERE user_id = ? AND is_default = 1 
            ORDER BY id ASC 
            LIMIT 1
        ');
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Adres ID'si ve kullanıcı ID'si ile adres bulur (güvenlik için)
     * 
     * @param int $addressId Adres ID'si
     * @param int $userId Kullanıcı ID'si
     * @return array|null Bulunan adres veya null
     */
    public static function findByIdAndUserId(int $addressId, int $userId): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$addressId, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Order Model - Sipariş işlemleri için Model sınıfı
 */
class Order extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'orders';
    }

    /**
     * Sipariş numarası ile sipariş bulur
     * 
     * @param string $orderNumber Sipariş numarası
     * @return array|null Bulunan sipariş veya null
     */
    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
        $stmt->execute([$orderNumber]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * E-posta ile son siparişi bulur (misafir siparişleri için)
     * 
     * @param string $email E-posta adresi
     * @return array|null Bulunan sipariş veya null
     */
    public static function findLatestByGuestEmail(string $email): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT * 
            FROM orders 
            WHERE LOWER(guest_email) = LOWER(?) 
            ORDER BY created_at DESC 
            LIMIT 1
        ');
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Kullanıcıya ait siparişleri getirir
     * 
     * @param int $userId Kullanıcı ID'si
     * @return array Siparişler
     */
    public static function getByUserId(int $userId): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT id, order_number, total, status, payment_method, created_at 
            FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sipariş kalemlerini getirir
     * 
     * @param int $orderId Sipariş ID'si
     * @return array Sipariş kalemleri
     */
    public static function getItems(int $orderId): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sipariş kargolarını getirir
     * 
     * @param int $orderId Sipariş ID'si
     * @return array Kargolar
     */
    public static function getShipments(int $orderId): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE order_id = ? ORDER BY id ASC');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Benzersiz sipariş numarası oluşturur
     * 
     * @return string Sipariş numarası
     */
    public static function generateOrderNumber(): string
    {
        $pdo = self::getConnection();
        $prefix = 'LB-' . date('Ymd') . '-';
        for ($i = 0; $i < 20; $i++) {
            $num = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $orderNumber = $prefix . $num;
            $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_number = ? LIMIT 1');
            $stmt->execute([$orderNumber]);
            if (!$stmt->fetch()) {
                return $orderNumber;
            }
        }
        return $prefix . uniqid();
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Coupon Model - Kupon işlemleri için Model sınıfı
 */
class Coupon extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'coupons';
    }

    /**
     * Kupon koduna göre aktif kupon bulur ve doğrular
     * 
     * @param string $code Kupon kodu
     * @param float $subtotal Sepet toplamı
     * @return array|null ['coupon' => array, 'discount' => float] veya null
     */
    public static function validate(string $code, float $subtotal): ?array
    {
        $pdo = self::getConnection();
        $code = strtoupper(trim($code));
        
        if ($code === '') {
            return null;
        }
        
        $stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            return null;
        }
        
        // Tarih kontrolü
        $now = date('Y-m-d H:i:s');
        if ($coupon['starts_at'] !== null && $coupon['starts_at'] > $now) {
            return null;
        }
        if ($coupon['ends_at'] !== null && $coupon['ends_at'] < $now) {
            return null;
        }
        
        // Minimum sipariş tutarı kontrolü
        $minOrder = $coupon['min_order_amount'] !== null ? (float) $coupon['min_order_amount'] : 0;
        if ($subtotal < $minOrder) {
            return null;
        }
        
        // Maksimum kullanım sayısı kontrolü
        $maxUse = $coupon['max_use_count'] ?? null;
        if ($maxUse !== null && (int) $coupon['used_count'] >= (int) $maxUse) {
            return null;
        }
        
        // İndirim hesaplama
        $discount = 0.0;
        $value = (float) $coupon['value'];
        if ($coupon['type'] === 'percent') {
            $discount = round($subtotal * $value / 100, 2);
            if ($coupon['max_discount'] !== null && $discount > (float) $coupon['max_discount']) {
                $discount = (float) $coupon['max_discount'];
            }
        } else {
            $discount = min($value, $subtotal);
        }
        $discount = max(0, $discount);
        
        return [
            'coupon' => $coupon,
            'discount' => $discount
        ];
    }
}

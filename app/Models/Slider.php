<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Slider Model - Slider işlemleri için Model sınıfı
 */
class Slider extends BaseModel
{
    protected static function getTableName(): string
    {
        return 'sliders';
    }

    /**
     * Aktif slider'ları getirir
     * 
     * @return array Slider'lar
     */
    public static function getActive(): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('
            SELECT id, title, subtitle, image, link, link_text
            FROM sliders
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

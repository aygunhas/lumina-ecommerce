<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateCouponsTable extends Migration
{
    public function getDescription(): string
    {
        return 'coupons tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS coupons (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                code VARCHAR(50) NOT NULL,
                type ENUM("percent","fixed") NOT NULL DEFAULT "percent",
                value DECIMAL(10,2) NOT NULL COMMENT "Yüzde veya sabit tutar",
                min_order_amount DECIMAL(12,2) DEFAULT NULL,
                max_use_count INT UNSIGNED DEFAULT NULL COMMENT "NULL = sınırsız",
                used_count INT UNSIGNED NOT NULL DEFAULT 0,
                starts_at TIMESTAMP NULL DEFAULT NULL,
                ends_at TIMESTAMP NULL DEFAULT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY code (code),
                KEY is_active (is_active),
                KEY starts_ends (starts_at, ends_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS coupons');
    }
}

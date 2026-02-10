<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateShippingMethodsTable extends Migration
{
    public function getDescription(): string
    {
        return 'shipping_methods tablosunu oluşturur (kargo yöntemleri)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS shipping_methods (
                id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                free_above DECIMAL(12,2) DEFAULT NULL COMMENT "Bu tutar üzeri ücretsiz kargo",
                estimated_days VARCHAR(50) DEFAULT NULL,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS shipping_methods');
    }
}

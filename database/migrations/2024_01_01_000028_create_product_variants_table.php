<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateProductVariantsTable extends Migration
{
    public function getDescription(): string
    {
        return 'product_variants tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS product_variants (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id INT UNSIGNED NOT NULL,
                sku VARCHAR(100) NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                price DECIMAL(12,2) DEFAULT NULL COMMENT "NULL ise ana ürün fiyatı kullanılır",
                sale_price DECIMAL(12,2) DEFAULT NULL,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY sku (sku),
                KEY product_id (product_id),
                CONSTRAINT product_variants_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS product_variants');
    }
}

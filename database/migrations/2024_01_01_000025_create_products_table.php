<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateProductsTable extends Migration
{
    public function getDescription(): string
    {
        return 'products tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS products (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                category_id INT UNSIGNED DEFAULT NULL,
                brand_id INT UNSIGNED DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                description TEXT,
                short_description VARCHAR(500) DEFAULT NULL,
                price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                sale_price DECIMAL(12,2) DEFAULT NULL COMMENT "İndirimli fiyat; NULL ise indirim yok",
                sku VARCHAR(100) DEFAULT NULL COMMENT "Ana ürün SKU (varyant yoksa kullanılır)",
                stock INT NOT NULL DEFAULT 0 COMMENT "Varyant yoksa toplam stok",
                low_stock_threshold SMALLINT UNSIGNED DEFAULT 5 COMMENT "Düşük stok uyarı eşiği",
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                is_new TINYINT(1) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(500) DEFAULT NULL,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                view_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug),
                KEY category_id (category_id),
                KEY brand_id (brand_id),
                KEY is_active (is_active),
                KEY is_featured (is_featured),
                KEY price (price),
                KEY created_at (created_at),
                CONSTRAINT products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL,
                CONSTRAINT products_brand FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS products');
    }
}

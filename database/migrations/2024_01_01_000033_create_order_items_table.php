<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateOrderItemsTable extends Migration
{
    public function getDescription(): string
    {
        return 'order_items tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS order_items (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id INT UNSIGNED NOT NULL,
                product_id INT UNSIGNED NOT NULL,
                product_variant_id INT UNSIGNED DEFAULT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_sku VARCHAR(100) NOT NULL,
                attributes_summary VARCHAR(255) DEFAULT NULL COMMENT "Beden, renk vb. metin",
                quantity INT UNSIGNED NOT NULL DEFAULT 1,
                price DECIMAL(12,2) NOT NULL,
                total DECIMAL(12,2) NOT NULL,
                PRIMARY KEY (id),
                KEY order_id (order_id),
                KEY product_id (product_id),
                CONSTRAINT order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
                CONSTRAINT order_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS order_items');
    }
}

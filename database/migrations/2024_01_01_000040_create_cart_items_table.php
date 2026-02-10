<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateCartItemsTable extends Migration
{
    public function getDescription(): string
    {
        return 'cart_items tablosunu oluşturur (kalıcı sepet)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS cart_items (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL,
                product_id INT UNSIGNED NOT NULL,
                product_variant_id INT UNSIGNED DEFAULT NULL,
                quantity INT UNSIGNED NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY user_product_variant (user_id, product_id, product_variant_id),
                KEY user_id (user_id),
                KEY product_id (product_id),
                CONSTRAINT cart_items_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                CONSTRAINT cart_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS cart_items');
    }
}

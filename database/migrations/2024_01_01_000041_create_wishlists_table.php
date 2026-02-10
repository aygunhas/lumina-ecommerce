<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateWishlistsTable extends Migration
{
    public function getDescription(): string
    {
        return 'wishlists tablosunu oluşturur (favori listesi)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS wishlists (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED NOT NULL,
                product_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY user_product (user_id, product_id),
                KEY user_id (user_id),
                KEY product_id (product_id),
                CONSTRAINT wishlists_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                CONSTRAINT wishlists_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS wishlists');
    }
}

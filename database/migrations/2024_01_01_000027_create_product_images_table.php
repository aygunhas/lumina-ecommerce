<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateProductImagesTable extends Migration
{
    public function getDescription(): string
    {
        return 'product_images tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS product_images (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id INT UNSIGNED NOT NULL,
                path VARCHAR(255) NOT NULL,
                alt VARCHAR(255) DEFAULT NULL,
                is_main TINYINT(1) NOT NULL DEFAULT 0 COMMENT "Ana görsel (kapak)",
                sort_order SMALLINT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY product_id (product_id),
                KEY is_main (is_main),
                CONSTRAINT product_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS product_images');
    }
}

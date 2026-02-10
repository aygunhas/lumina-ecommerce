<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateProductTranslationsTable extends Migration
{
    public function getDescription(): string
    {
        return 'product_translations tablosunu oluşturur (ürün çevirileri)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS product_translations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id INT UNSIGNED NOT NULL,
                locale VARCHAR(5) NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                short_description VARCHAR(500) DEFAULT NULL,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(500) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY product_locale (product_id, locale),
                KEY locale (locale),
                CONSTRAINT product_translations_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS product_translations');
    }
}

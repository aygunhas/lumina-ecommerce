<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateCategoryTranslationsTable extends Migration
{
    public function getDescription(): string
    {
        return 'category_translations tablosunu oluşturur (kategori çevirileri)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS category_translations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                category_id INT UNSIGNED NOT NULL,
                locale VARCHAR(5) NOT NULL,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(500) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY category_locale (category_id, locale),
                KEY locale (locale),
                CONSTRAINT category_translations_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS category_translations');
    }
}

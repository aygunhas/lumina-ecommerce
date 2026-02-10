<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreatePageTranslationsTable extends Migration
{
    public function getDescription(): string
    {
        return 'page_translations tablosunu oluşturur (sayfa çevirileri)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS page_translations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                page_id INT UNSIGNED NOT NULL,
                locale VARCHAR(5) NOT NULL,
                title VARCHAR(255) NOT NULL,
                content LONGTEXT,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(500) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY page_locale (page_id, locale),
                KEY locale (locale),
                CONSTRAINT page_translations_page FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS page_translations');
    }
}

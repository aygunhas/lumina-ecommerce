<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateCategoriesTable extends Migration
{
    public function getDescription(): string
    {
        return 'categories tablosunu oluşturur (hiyerarşik kategori)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                parent_id INT UNSIGNED DEFAULT NULL,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                description TEXT,
                image VARCHAR(255) DEFAULT NULL,
                home_hero_text VARCHAR(255) DEFAULT NULL COMMENT "Ana sayfa kategori containerında gösterilecek metin (örn: Koleksiyonu Keşfet)",
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(500) DEFAULT NULL,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug),
                KEY parent_id (parent_id),
                KEY is_active (is_active),
                KEY sort_order (sort_order),
                CONSTRAINT categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS categories');
    }
}

<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreatePagesTable extends Migration
{
    public function getDescription(): string
    {
        return 'pages tablosunu oluşturur (sabit sayfalar)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS pages (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                slug VARCHAR(100) NOT NULL,
                title VARCHAR(255) NOT NULL,
                content LONGTEXT,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(500) DEFAULT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug),
                KEY is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS pages');
    }
}

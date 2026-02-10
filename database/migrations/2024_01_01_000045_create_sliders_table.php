<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateSlidersTable extends Migration
{
    public function getDescription(): string
    {
        return 'sliders tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS sliders (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) DEFAULT NULL,
                subtitle VARCHAR(255) DEFAULT NULL,
                image VARCHAR(255) NOT NULL,
                link VARCHAR(500) DEFAULT NULL,
                link_text VARCHAR(100) DEFAULT NULL,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY is_active (is_active),
                KEY sort_order (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS sliders');
    }
}

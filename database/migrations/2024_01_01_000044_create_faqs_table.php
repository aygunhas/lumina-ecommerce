<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateFaqsTable extends Migration
{
    public function getDescription(): string
    {
        return 'faqs tablosunu oluşturur (SSS)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS faqs (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                question VARCHAR(500) NOT NULL,
                answer TEXT,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY sort_order (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS faqs');
    }
}

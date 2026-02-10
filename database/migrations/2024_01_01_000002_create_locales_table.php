<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateLocalesTable extends Migration
{
    public function getDescription(): string
    {
        return 'locales tablosunu oluşturur (çoklu dil desteği)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS locales (
                id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
                code VARCHAR(5) NOT NULL,
                name VARCHAR(50) NOT NULL,
                is_default TINYINT(1) NOT NULL DEFAULT 0,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS locales');
    }
}

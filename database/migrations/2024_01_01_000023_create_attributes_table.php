<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAttributesTable extends Migration
{
    public function getDescription(): string
    {
        return 'attributes tablosunu oluşturur (beden/renk özellikleri)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS attributes (
                id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
                type ENUM("size","color","other") NOT NULL DEFAULT "other",
                name VARCHAR(50) NOT NULL,
                slug VARCHAR(50) NOT NULL,
                sort_order SMALLINT NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug),
                KEY type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS attributes');
    }
}

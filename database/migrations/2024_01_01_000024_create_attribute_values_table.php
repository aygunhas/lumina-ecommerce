<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAttributeValuesTable extends Migration
{
    public function getDescription(): string
    {
        return 'attribute_values tablosunu oluşturur (özellik değerleri)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS attribute_values (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                attribute_id TINYINT UNSIGNED NOT NULL,
                value VARCHAR(100) NOT NULL,
                color_hex VARCHAR(7) DEFAULT NULL COMMENT "Renk için hex kodu",
                sort_order SMALLINT NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                KEY attribute_id (attribute_id),
                CONSTRAINT attribute_values_attribute FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS attribute_values');
    }
}

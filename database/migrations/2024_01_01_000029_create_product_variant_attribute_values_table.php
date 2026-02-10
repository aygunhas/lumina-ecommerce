<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateProductVariantAttributeValuesTable extends Migration
{
    public function getDescription(): string
    {
        return 'product_variant_attribute_values tablosunu oluşturur (varyant-özellik ilişkisi)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS product_variant_attribute_values (
                variant_id INT UNSIGNED NOT NULL,
                attribute_value_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (variant_id, attribute_value_id),
                KEY attribute_value_id (attribute_value_id),
                CONSTRAINT pvav_variant FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE CASCADE,
                CONSTRAINT pvav_attribute_value FOREIGN KEY (attribute_value_id) REFERENCES attribute_values (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS product_variant_attribute_values');
    }
}

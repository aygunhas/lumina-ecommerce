<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class AddAttributeValueIdToProductImages extends Migration
{
    public function getDescription(): string
    {
        return 'product_images tablosuna attribute_value_id sütunu ekler (renk bazlı fotoğraflar için)';
    }

    public function up(): void
    {
        $this->execute('
            ALTER TABLE product_images 
            ADD COLUMN attribute_value_id INT UNSIGNED DEFAULT NULL 
            COMMENT "Renk bazlı fotoğraflar için attribute_value_id (renk ID)" 
            AFTER product_id,
            ADD KEY attribute_value_id (attribute_value_id),
            ADD CONSTRAINT product_images_attribute_value 
                FOREIGN KEY (attribute_value_id) 
                REFERENCES attribute_values (id) 
                ON DELETE CASCADE
        ');
    }

    public function down(): void
    {
        $this->execute('
            ALTER TABLE product_images 
            DROP FOREIGN KEY product_images_attribute_value,
            DROP KEY attribute_value_id,
            DROP COLUMN attribute_value_id
        ');
    }
}

<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class AddMaterialCareToProducts extends Migration
{
    public function getDescription(): string
    {
        return 'products tablosuna material_care sütunu ekler';
    }

    public function up(): void
    {
        $this->execute('
            ALTER TABLE products 
            ADD COLUMN material_care TEXT DEFAULT NULL COMMENT "Materyal ve bakım bilgileri" 
            AFTER description
        ');
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE products DROP COLUMN material_care');
    }
}

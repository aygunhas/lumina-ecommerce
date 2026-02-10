<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class MoveXxsToTop extends Migration
{
    public function getDescription(): string
    {
        return 'XXS bedenini beden listesinin en başına taşır';
    }

    public function up(): void
    {
        // XXS bedenini en başa al (sort_order = -10)
        // Beden attribute ID'sini bul ve XXS'in sort_order değerini güncelle
        $this->execute('
            UPDATE attribute_values av
            INNER JOIN attributes a ON av.attribute_id = a.id
            SET av.sort_order = -10
            WHERE a.slug = "beden" AND av.value = "XXS"
        ');
    }

    public function down(): void
    {
        // Geri alma: XXS'i orijinal sort_order değerine (0) geri al
        $this->execute('
            UPDATE attribute_values av
            INNER JOIN attributes a ON av.attribute_id = a.id
            SET av.sort_order = 0
            WHERE a.slug = "beden" AND av.value = "XXS"
        ');
    }
}

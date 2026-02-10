<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAdminPermissionsTable extends Migration
{
    public function getDescription(): string
    {
        return 'admin_permissions tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS admin_permissions (
                id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                group_name VARCHAR(50) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS admin_permissions');
    }
}

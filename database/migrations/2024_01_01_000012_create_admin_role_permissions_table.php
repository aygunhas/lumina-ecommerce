<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAdminRolePermissionsTable extends Migration
{
    public function getDescription(): string
    {
        return 'admin_role_permissions tablosunu oluşturur (rol-yetki ilişkisi)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS admin_role_permissions (
                role_id TINYINT UNSIGNED NOT NULL,
                permission_id SMALLINT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                KEY permission_id (permission_id),
                CONSTRAINT admin_role_permissions_role FOREIGN KEY (role_id) REFERENCES admin_roles (id) ON DELETE CASCADE,
                CONSTRAINT admin_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES admin_permissions (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS admin_role_permissions');
    }
}

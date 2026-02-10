<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateEmailTemplatesTable extends Migration
{
    public function getDescription(): string
    {
        return 'email_templates tablosunu oluşturur (e-posta şablonları)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS email_templates (
                id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
                slug VARCHAR(50) NOT NULL COMMENT "order_confirmation, shipping_info, password_reset vb.",
                name VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                body_html TEXT,
                body_text TEXT,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS email_templates');
    }
}

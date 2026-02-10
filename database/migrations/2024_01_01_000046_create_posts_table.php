<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreatePostsTable extends Migration
{
    public function getDescription(): string
    {
        return 'posts tablosunu oluşturur (blog/haber)';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS posts (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                admin_user_id INT UNSIGNED DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                excerpt VARCHAR(500) DEFAULT NULL,
                content LONGTEXT,
                image VARCHAR(255) DEFAULT NULL,
                is_published TINYINT(1) NOT NULL DEFAULT 0,
                published_at TIMESTAMP NULL DEFAULT NULL,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(500) DEFAULT NULL,
                view_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug),
                KEY admin_user_id (admin_user_id),
                KEY is_published (is_published),
                KEY published_at (published_at),
                CONSTRAINT posts_author FOREIGN KEY (admin_user_id) REFERENCES admin_users (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS posts');
    }
}

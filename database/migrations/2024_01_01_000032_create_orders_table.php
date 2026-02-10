<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateOrdersTable extends Migration
{
    public function getDescription(): string
    {
        return 'orders tablosunu oluşturur';
    }

    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS orders (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED DEFAULT NULL,
                order_number VARCHAR(50) NOT NULL,
                status ENUM("pending","confirmed","processing","shipped","delivered","cancelled","refunded") NOT NULL DEFAULT "pending",
                payment_method ENUM("cod","bank_transfer","stripe") NOT NULL,
                payment_status ENUM("pending","paid","failed","refunded") NOT NULL DEFAULT "pending",
                stripe_payment_intent_id VARCHAR(255) DEFAULT NULL,
                stripe_session_id VARCHAR(255) DEFAULT NULL,
                subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                coupon_id INT UNSIGNED DEFAULT NULL,
                guest_email VARCHAR(255) DEFAULT NULL,
                guest_first_name VARCHAR(100) DEFAULT NULL,
                guest_last_name VARCHAR(100) DEFAULT NULL,
                guest_phone VARCHAR(20) DEFAULT NULL,
                shipping_first_name VARCHAR(100) NOT NULL,
                shipping_last_name VARCHAR(100) NOT NULL,
                shipping_phone VARCHAR(20) NOT NULL,
                shipping_city VARCHAR(100) NOT NULL,
                shipping_district VARCHAR(100) NOT NULL,
                shipping_address_line VARCHAR(255) NOT NULL,
                shipping_postal_code VARCHAR(20) DEFAULT NULL,
                billing_same_as_shipping TINYINT(1) NOT NULL DEFAULT 1,
                billing_first_name VARCHAR(100) DEFAULT NULL,
                billing_last_name VARCHAR(100) DEFAULT NULL,
                billing_phone VARCHAR(20) DEFAULT NULL,
                billing_city VARCHAR(100) DEFAULT NULL,
                billing_district VARCHAR(100) DEFAULT NULL,
                billing_address_line VARCHAR(255) DEFAULT NULL,
                billing_postal_code VARCHAR(20) DEFAULT NULL,
                customer_notes TEXT,
                admin_notes TEXT,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY order_number (order_number),
                KEY user_id (user_id),
                KEY status (status),
                KEY payment_status (payment_status),
                KEY created_at (created_at),
                KEY coupon_id (coupon_id),
                CONSTRAINT orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
                CONSTRAINT orders_coupon FOREIGN KEY (coupon_id) REFERENCES coupons (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS orders');
    }
}

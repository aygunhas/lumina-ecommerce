-- Admin bildirim tablosu (tablo yoksa çalıştırın)
-- Örnek: mysql -u root -p lumina_db < database/seeds/create_admin_notifications_table.sql

CREATE TABLE IF NOT EXISTS `admin_notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_notifications` (`title`, `message`, `is_read`, `link`) VALUES
('Yeni Sipariş', '#TR-8842 nolu yeni bir sipariş alındı.', 0, '/admin/orders/show?id=1'),
('Stok Uyarısı', 'Siyah Tişört (L) stokları kritik seviyede.', 0, '/admin/products/edit?id=3'),
('Yeni Üye', 'Ahmet Yılmaz sisteme kayıt oldu.', 1, '/admin/customers/show?id=5');

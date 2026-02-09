-- =============================================================================
-- Beden ve Renk özellikleri için örnek veri (varyant altyapısı)
-- Çalıştırma: phpMyAdmin veya MySQL komut satırından çalıştırın
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1) Beden (size) özelliğini ekle (eğer yoksa)
INSERT IGNORE INTO `attributes` (`type`, `name`, `slug`, `sort_order`) 
VALUES ('size', 'Beden', 'beden', 10);

-- Beden ID'sini al
SET @beden_id = (SELECT id FROM attributes WHERE slug = 'beden' LIMIT 1);

-- Beden değerlerini ekle (eğer yoksa)
INSERT IGNORE INTO `attribute_values` (`attribute_id`, `value`, `color_hex`, `sort_order`) VALUES
(@beden_id, 'XXS', NULL, 0),
(@beden_id, 'XS', NULL, 10),
(@beden_id, 'S', NULL, 20),
(@beden_id, 'M', NULL, 30),
(@beden_id, 'L', NULL, 40),
(@beden_id, 'XL', NULL, 50),
(@beden_id, 'XXL', NULL, 60),
(@beden_id, 'XXXL', NULL, 70),
(@beden_id, '36', NULL, 80),
(@beden_id, '38', NULL, 90),
(@beden_id, '40', NULL, 100),
(@beden_id, '42', NULL, 110),
(@beden_id, '44', NULL, 120),
(@beden_id, '46', NULL, 130),
(@beden_id, '48', NULL, 140),
(@beden_id, '50', NULL, 150),
(@beden_id, '52', NULL, 160);

-- 2) Renk (color) özelliğini ekle (eğer yoksa)
INSERT IGNORE INTO `attributes` (`type`, `name`, `slug`, `sort_order`) 
VALUES ('color', 'Renk', 'renk', 20);

-- Renk ID'sini al
SET @renk_id = (SELECT id FROM attributes WHERE slug = 'renk' LIMIT 1);

-- Renk değerlerini ekle (eğer yoksa)
INSERT IGNORE INTO `attribute_values` (`attribute_id`, `value`, `color_hex`, `sort_order`) VALUES
(@renk_id, 'Kırmızı', '#c62828', 0),
(@renk_id, 'Mavi', '#1565c0', 10),
(@renk_id, 'Siyah', '#212121', 20),
(@renk_id, 'Beyaz', '#fafafa', 30),
(@renk_id, 'Gri', '#757575', 40),
(@renk_id, 'Lacivert', '#0d47a1', 50),
(@renk_id, 'Yeşil', '#2e7d32', 60),
(@renk_id, 'Sarı', '#f9a825', 70),
(@renk_id, 'Turuncu', '#ef6c00', 80),
(@renk_id, 'Pembe', '#c2185b', 90),
(@renk_id, 'Mor', '#6a1b9a', 100),
(@renk_id, 'Kahverengi', '#5d4037', 110),
(@renk_id, 'Bej', '#d7ccc8', 120),
(@renk_id, 'Krem', '#fff9e6', 130),
(@renk_id, 'Bordo', '#880e4f', 140),
(@renk_id, 'Turkuaz', '#00897b', 150),
(@renk_id, 'Lavanta', '#9575cd', 160),
(@renk_id, 'Füme', '#78909c', 170),
(@renk_id, 'Haki', '#827717', 180),
(@renk_id, 'Navy', '#0d47a1', 190),
(@renk_id, 'Koyu Gri', '#424242', 200),
(@renk_id, 'Açık Gri', '#bdbdbd', 210),
(@renk_id, 'Altın', '#ffa000', 220),
(@renk_id, 'Gümüş', '#9e9e9e', 230);

SET FOREIGN_KEY_CHECKS = 1;

-- Seed tamamlandı. Panelden Admin → Özellikler (Beden/Renk) ile yönetebilirsiniz.

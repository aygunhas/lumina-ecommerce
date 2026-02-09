-- product_images tablosuna is_main sütunu ekleme
-- Bu dosyayı çalıştırmadan önce veritabanınızı yedekleyin

-- Sütunun var olup olmadığını kontrol et ve yoksa ekle
SET @dbname = DATABASE();
SET @tablename = 'product_images';
SET @columnname = 'is_main';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT 1', -- Sütun zaten var, hiçbir şey yapma
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` tinyint(1) NOT NULL DEFAULT 0 COMMENT ''Ana görsel (kapak)'' AFTER `alt`;')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Mevcut görsellerden ilkini ana görsel yap (her ürün için)
-- MySQL'de aynı tabloyu UPDATE ve FROM'da kullanamayız, bu yüzden geçici tablo kullanıyoruz

-- Her product_id için sort_order'a göre sıralanmış ilk görseli bul
CREATE TEMPORARY TABLE temp_first_images AS
SELECT 
    p1.product_id,
    MIN(p1.id) as first_id
FROM product_images p1
INNER JOIN (
    SELECT product_id, MIN(sort_order) as min_sort_order
    FROM product_images
    GROUP BY product_id
) p2 ON p1.product_id = p2.product_id AND p1.sort_order = p2.min_sort_order
GROUP BY p1.product_id;

-- Bulunan ilk görselleri ana görsel yap
UPDATE product_images pi1
INNER JOIN temp_first_images tfi ON pi1.product_id = tfi.product_id AND pi1.id = tfi.first_id
SET pi1.is_main = 1;

-- Geçici tabloyu temizle
DROP TEMPORARY TABLE temp_first_images;

-- İndeks ekle (eğer yoksa)
SET @indexname = 'is_main';
SET @tablename = 'product_images';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE
            (TABLE_SCHEMA = DATABASE())
            AND (TABLE_NAME = @tablename)
            AND (INDEX_NAME = @indexname)
    ) > 0,
    'SELECT 1', -- İndeks zaten var, hiçbir şey yapma
    CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`', @indexname, '`);')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

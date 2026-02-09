-- Ana sayfa kategori hero metni sütunu ekleme
-- Bu dosyayı çalıştırmadan önce veritabanınızı yedekleyin

ALTER TABLE `categories` 
ADD COLUMN `home_hero_text` varchar(255) DEFAULT NULL 
COMMENT 'Ana sayfa kategori containerında gösterilecek metin (örn: Koleksiyonu Keşfet)' 
AFTER `image`;

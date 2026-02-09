-- Dashboard performans indeksleri
-- Bu dosyayı sadece bir kez çalıştırın. İndeks zaten varsa hata verebilir, bu normaldir.

-- Siparişler tablosuna tarih ve durum bazlı indeks (dashboard sorgularını hızlandırır)
ALTER TABLE `orders` ADD INDEX `idx_created_status` (`created_at`, `status`);

-- Sipariş kalemleri tablosuna ürün ve miktar bazlı indeks (en çok satanlar sorgusu için)
ALTER TABLE `order_items` ADD INDEX `idx_product_quantity` (`product_id`, `quantity`);

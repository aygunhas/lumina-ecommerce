-- order_items tablosundaki product_id foreign key constraint'ini kaldırma
-- Bu dosyayı çalıştırmadan önce veritabanınızı yedekleyin
-- 
-- NOT: Bu constraint kaldırıldıktan sonra, ürün silme kontrolü uygulama seviyesinde yapılacak
-- Aktif siparişlerde (pending, confirmed, processing) kullanılan ürünler silinemez
-- Diğer durumlarda (delivered, cancelled, refunded, shipped) silinebilir

-- Mevcut constraint'i kaldır
ALTER TABLE `order_items` DROP FOREIGN KEY `order_items_product`;

-- NOT: Constraint kaldırıldıktan sonra, ürün silme işlemi uygulama seviyesinde kontrol edilecek
-- ProductsController::delete() metodu aktif siparişlerde kullanılan ürünleri kontrol ediyor

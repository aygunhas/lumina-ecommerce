-- Eksik sayfaları ekler (Gizlilik Politikası, Çerez Politikası)
-- PHP kurmadan: Bu dosyayı phpMyAdmin, MySQL Workbench veya hosting panelinde "SQL çalıştır" ile çalıştırın.
-- Sayfa zaten varsa (aynı slug) satır atlanır, hata vermez.

INSERT IGNORE INTO pages (slug, title, content, is_active, sort_order, created_at, updated_at) VALUES
('gizlilik', 'Gizlilik Politikası', '<h2 id="veri-toplama">Veri Toplama</h2><p>Bu sayfada kişisel verilerinizin hangi amaçlarla toplandığı ve nasıl işlendiği yer alacaktır. İçerik panelden düzenlenebilir.</p><h2 id="haklariniz">Haklarınız</h2><p>KVKK kapsamındaki haklarınız ve başvuru yöntemleri bu bölümde açıklanacaktır.</p>', 1, 35, NOW(), NOW()),
('cerez-politikasi', 'Çerez Politikası', '<h2 id="cerezler">Çerezler</h2><p>Web sitemizde kullanılan çerez türleri ve amaçları bu sayfada açıklanacaktır. İçerik panelden düzenlenebilir.</p><h2 id="yonetim">Çerez Yönetimi</h2><p>Tarayıcı ayarlarından çerez tercihlerinizi nasıl yönetebileceğiniz anlatılacaktır.</p>', 1, 45, NOW(), NOW());

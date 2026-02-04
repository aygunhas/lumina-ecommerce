# Lumina Boutique – Proje El Kitabı, Test Mantığı ve Kalan Gruplar (Ultra Detaylı)

Bu dosya **yeni bir chat ile devam etmek** için hazırlanmıştır. Projenin mevcut durumu, test/yönlendirme mantığı, tamamlanan işler ve **yapılması gereken tüm işler** tek yerde, adım adım açıklanmıştır. Yeni chat’te bu dosyayı referans alarak “Grup 5’ten devam et” veya “Grup 6’yı yap” denildiğinde tüm bağlam burada bulunur.

---

## İçindekiler

1. [Proje Özeti ve Teknik Yapı](#1-proje-özeti-ve-teknik-yapı)
2. [Test ve Yönlendirme Mantığı (TEST_VE_YONLENDIRME.md Kullanımı)](#2-test-ve-yönlendirme-mantığı)
3. [Tamamlanan Gruplar (1–4) – Referans](#3-tamamlanan-gruplar-14)
4. [Kalan Grup 5 – İçerik ve Yasal (Detaylı İş Listesi)](#4-kalan-grup-5-içerik-ve-yasal)
5. [Kalan Grup 6 – İsteğe Bağlı / İleri (Detaylı İş Listesi)](#5-kalan-grup-6-isteğe-bağlı)
6. [Dosya Yapısı ve Kritik Yollar](#6-dosya-yapısı-ve-kritik-yollar)
7. [Veritabanı Şeması (İlgili Tablolar)](#7-veritabanı-şeması)
8. [Yeni Chat İçin Kısa Talimat](#8-yeni-chat-için-kısa-talimat)

---

## 1. Proje Özeti ve Teknik Yapı

- **Proje:** Lumina Boutique – PHP tabanlı e-ticaret (mağaza + yönetim paneli).
- **Ortam:** MAMP (Apache + MySQL). Document root `public`, tüm istekler `public/index.php` üzerinden.
- **Dil:** PHP 7.4+ (strict_types), vanilla HTML/CSS (framework yok).
- **Veritabanı:** MySQL, PDO. Şema: `database/schema.sql`. Bağlantı: `App\Config\Database::getConnection()`.
- **Router:** `config/routes.php` → `App\Router`. Pattern’de `:slug` veya `:id` varsa yakalanan değer sırasıyla `$_GET['_slug']` veya `$_GET['_id']` olarak controller’a iletilir.
- **Middleware:** Rota dizisinde 3. eleman: `['admin']` → `App\Middleware\AdminAuth`, `['user']` → `App\Middleware\UserAuth`. Admin oturumu yoksa `/admin/login`, üye oturumu yoksa `/giris` yönlendirmesi.
- **Görünüm:** Controller’lar kendi `render()` metodları ile `app/Views/` altındaki PHP şablonlarını yükler; layout `app/Views/frontend/layouts/main.php` veya `app/Views/admin/layouts/main.php`.

**Önemli dosyalar:**
- Giriş noktası: `public/index.php`
- Rota tanımları: `config/routes.php`
- Router sınıfı: `app/Router.php`
- Bootstrap (env, autoload): `config/bootstrap.php`
- Ortam değişkenleri: `.env` (DB_*, APP_NAME vb.)

---

## 2. Test ve Yönlendirme Mantığı

**`docs/TEST_VE_YONLENDIRME.md`** dosyasının kullanım mantığı:

- **Amaç:** Projede “şu an ne var?”, “nasıl test edilir?”, “sırada ne gelecek?” sorularının cevabını tek yerde tutmak.
- **Güncelleme kuralı:** Her yeni özellik eklendiğinde bu rehber güncellenir; böylece elle test ve ilerideki geliştirme takibi yapılır.
- **İçerik yapısı:**
  1. **Özet tablo:** Nerede (Mağaza/Admin) → Ne var → Nasıl açılır (URL).
  2. **Adım adım test:** Her bölüm için “1. Şunu aç, 2. Şunu yap, 3. Şunu gör” tarzı senaryolar.
  3. **Sırada ne gelecek tablosu:** Tamamlanan özellikler üstü çizili; yeni eklenecek özellikler ve nerede test edileceği.

**Test mantığı özeti:**
- Mağaza: Tarayıcıda site adresi (örn. `http://localhost:8888/`). Header’da Anasayfa, Giriş/Kayıt veya Hesabım/Çıkış, Sepet, Arama formu.
- Admin: `http://localhost:8888/admin/login` → E-posta: **admin@luminaboutique.com**, Şifre: **Admin123!** (seed: `database/seeds/seed_admin.php`).
- 404 / veritabanı hatası: `docs/MAMP_APACHE_AYARLARI.md`, `.env`, `docs/SCHEMA_NASIL_CALISTIRILIR.md` referans alınır.

**Yeni chat’te:** Yeni bir özellik eklendikten sonra `TEST_VE_YONLENDIRME.md` içindeki “Sırada ne gelecek?” tablosuna bir satır eklenmeli veya ilgili satır “Tamamlandı” ile güncellenmeli; “Adım adım test” bölümüne yeni senaryo eklenebilir.

---

## 3. Tamamlanan Gruplar (1–4)

Aşağıdaki gruplar **tamamlanmış** kabul edilir. Referans için kısa özet ve ilgili dosya/rota verilmiştir.

### Grup 1 – Faz 1 tamamlama ✅

| İşlev | Rota / Yer | Dosya / Not |
|-------|------------|-------------|
| Sipariş yazdırma | `/admin/orders/show` → **Yazdır** butonu; `/admin/orders/print?id=X` | `OrdersController::print()`, `app/Views/admin/orders/print.php` |
| Site ayarları (B35) | `/admin/settings` | `SettingsController`, `app/Helpers/Settings.php`, tablo `settings` (group_name, key, value) |
| Kargo ayarları (B36) | Aynı sayfa: shipping_cost, free_shipping_min | Checkout’ta kargo hesaplama bu ayarlardan okunur |
| Ödeme ayarları (B37) | Aynı sayfa: cod_enabled, bank_transfer_enabled, bank_name, bank_iban, bank_account_name | Mağaza ödeme sayfası bu ayarlara göre seçenek gösterir |
| Düşük stok uyarısı (B8) | `/admin` (Dashboard) | `DashboardController`, dashboard view’da stok eşiğinin altındaki ürünler listesi |
| Ürün görseli silme (B14) | `/admin/products/edit` → görsel yanında **Kaldır** | `ProductsController::deleteImage()`, POST `/admin/products/delete-image` |
| Sipariş listesi arama/filtre (B20) | `/admin/orders` | GET: order_no, customer_name, email, status, date_from, date_to → filtreleme |
| Ürün listesi arama/filtre (B9) | `/admin/products` | GET: search (ad/SKU), category_id, stock_status (in_stock/low/none) |

### Grup 2 – Üye ve hesap ✅

| İşlev | Rota | Dosya / Not |
|-------|------|-------------|
| Üye kayıt | `/kayit` | `UserAuthController::registerForm()` + POST işleme, `app/Views/frontend/auth/register.php` |
| Giriş/Çıkış | `/giris`, `/cikis` | `UserAuthController::loginForm()`, logout; session `user_id` |
| Hesabım | `/hesabim` | `AccountController::index()` ['user'], `app/Views/frontend/account/index.php` |
| Siparişlerim | `/hesabim/siparisler`, `/hesabim/siparisler/show?id=` | `AccountController::orders()`, `orderShow()` |
| Adreslerim | `/hesabim/adresler`, ekle/duzenle/sil | `AccountController::addresses()`, addressCreate, addressEdit, addressDelete |
| Bilgilerim (profil) | `/hesabim/bilgilerim` | `AccountController::profile()` – ad, telefon, şifre güncelleme |
| Üye ile sipariş | `/odeme` | Giriş varsa “Kayıtlı adresim” seçimi; siparişe `user_id` yazılır |
| Admin Müşteriler | `/admin/customers`, `/admin/customers/show?id=` | `CustomersController`, liste + detay (sipariş geçmişi, adresler), arama: ad, e-posta, telefon |

### Grup 3 – Kupon ve raporlar ✅

| İşlev | Rota | Dosya / Not |
|-------|------|-------------|
| Kupon CRUD | `/admin/coupons`, create, edit, delete | `CouponsController`, `app/Views/admin/coupons/` |
| Sepette/Ödemede kupon | `/odeme` formunda kupon kodu | `CheckoutController`: kupon doğrulama, indirim hesaplama; siparişe `coupon_id`, `discount_amount`; `used_count` artırımı |
| Satış raporu | `/admin/reports/sales` | `ReportsController::sales()` – tarih aralığı, sipariş sayısı, toplam satış, en çok satan ürünler |
| Stok raporu | `/admin/reports/stock` | `ReportsController::stock()` – stok listesi, düşük stok uyarısı |

### Grup 4 – Katalog ve bulunabilirlik ✅

| İşlev | Rota | Dosya / Not |
|-------|------|-------------|
| Ürün arama | `/arama?q=` | `SearchController::index()`, header’da arama formu; ürün adı, SKU, short_description LIKE |
| Sıralama | Kategori: `/kategori/:slug`, Arama: `/arama` | `CategoryController::show()`, `SearchController::index()` – sort=price_asc|price_desc|newest|name, per_page=12|24 |
| Sayfalama | Aynı sayfalar | page parametresi, LIMIT/OFFSET |
| Öne çıkan / Yeni / % İndirim etiketleri | Anasayfa, kategori, arama, **ürün detay** | `home.php`, `category/show.php`, `search/index.php`, `product/show.php` – is_featured, is_new, sale_price ile etiket ve indirim yüzdesi |

---

## 4. Kalan Grup 5 – İçerik ve Yasal (Detaylı İş Listesi)

Bu grup **yapılması gereken** işleri adım adım tanımlar. Sıra önerisi: Önce sabit sayfalar altyapısı (panel + mağaza slug), sonra SSS, İade, KVKK, Mesafeli satış sayfaları ve footer linkleri.

### 4.1 Sabit sayfalar panelden (B32) + Mağaza /sayfa/slug

**Amaç:** Hakkımızda, İletişim metni, SSS, İade, KVKK, Mesafeli satış gibi metinler `pages` tablosundan panelden düzenlensin; mağaza tarafında slug ile tek bir rota üzerinden gösterilsin.

**Veritabanı:** `pages` tablosu mevcut (`database/schema.sql`):

- `id`, `slug` (UNIQUE), `title`, `content` (longtext), `meta_title`, `meta_description`, `is_active`, `sort_order`, `created_at`, `updated_at`.

**Yapılacaklar:**

1. **Admin: Sayfalar listesi ve CRUD**
   - **Rota:** `/admin/pages` (liste), `/admin/pages/create`, `/admin/pages/edit?id=`, `/admin/pages/delete?id=` (isteğe bağlı silme veya sadece pasif yapma).
   - **Controller:** Yeni `App\Controllers\Admin\PagesController` (veya mevcut bir controller’a eklenmez; ayrı sınıf önerilir).
   - **View:** `app/Views/admin/pages/index.php` (liste: slug, title, durum, düzenle linki), `form.php` veya create/edit (slug, title, content textarea, meta_title, meta_description, is_active).
   - **Liste:** `SELECT id, slug, title, is_active, sort_order FROM pages ORDER BY sort_order, title`.
   - **Kaydetme:** INSERT/UPDATE `pages`. Slug benzersiz olmalı; formda slug düzenlenebilir veya title’dan otomatik üretilebilir.

2. **Mağaza: Slug ile sayfa gösterme**
   - **Rota:** `/sayfa/:slug` (örn. `/sayfa/hakkimizda`, `/sayfa/sss`, `/sayfa/iade-kosullari`, `/sayfa/kvkk`, `/sayfa/mesafeli-satis-sozlesmesi`).
   - **Router:** `config/routes.php` içine ekle: `'/sayfa/:slug' => [PageController::class, 'showBySlug', []]`.
   - **Controller:** `App\Controllers\Frontend\PageController` içine `showBySlug()` metodu:
     - `$_GET['_slug']` ile slug alınır.
     - `SELECT * FROM pages WHERE slug = ? AND is_active = 1 LIMIT 1`.
     - Kayıt yoksa 404.
     - View’a `title`, `content`, `baseUrl` vb. gönderilir; layout ile sayfa içeriği gösterilir.
   - **View:** `app/Views/frontend/pages/show.php` (veya `page.php`) – başlık ve `content` (HTML veya nl2br ile gösterim; XSS için htmlspecialchars/content güvenliği düşünülmeli).

3. **Hakkımızda’yı pages’e taşıma (opsiyonel)**
   - Şu an `/hakkimizda` → `PageController::about()` sabit view (`frontend/pages/about.php`) kullanıyor.
   - İstenirse `/hakkimizda` yönlendirmesi kaldırılıp sadece `/sayfa/hakkimizda` kullanılır ve `pages` tablosunda `slug=hakkimizda` kaydı ile içerik panelden yönetilir. Veya `/hakkimizda` rotası kalır ama içerik `pages` tablosundan `slug=hakkimizda` ile çekilir.

**Seed (isteğe bağlı):** İlk kurulumda `pages` tablosuna SSS, İade, KVKK, Mesafeli satış, Hakkımızda için örnek satırlar eklenebilir (slug, title, content placeholder).

---

### 4.2 SSS sayfası (A36)

- **Mağaza URL:** `/sayfa/sss` veya ayrı rota `/sss` (tercih projeye göre).
- **İçerik:** `pages` tablosunda `slug=sss` kaydı; panelden başlık ve içerik (sıkça sorulan sorular cevapları) düzenlenir.
- **Footer:** Footer’da “SSS” linki → `/sayfa/sss` veya `/sss` (rota ne ise).

---

### 4.3 İade & değişim (A37)

- **Mağaza URL:** `/sayfa/iade-kosullari` (veya slug farklı: `iade-degisim`).
- **İçerik:** `pages` tablosunda ilgili slug; panelden metin düzenlenir.
- **Footer:** “İade koşulları” linki eklenir.

---

### 4.4 Gizlilik & KVKK (A38)

- **Mağaza URL:** `/sayfa/kvkk` veya `/sayfa/gizlilik`.
- **İçerik:** `pages` tablosu.
- **Footer:** “Gizlilik / KVKK” linki.

---

### 4.5 Mesafeli satış sözleşmesi (A39)

- **Mağaza URL:** `/sayfa/mesafeli-satis-sozlesmesi`.
- **İçerik:** `pages` tablosu.
- **Footer:** “Mesafeli satış sözleşmesi” linki.

---

### 4.6 Footer güncellemesi

- **Dosya:** `app/Views/frontend/layouts/main.php`.
- **Mevcut:** İletişim, Hakkımızda linkleri.
- **Eklenecek:** SSS, İade koşulları, KVKK (veya Gizlilik), Mesafeli satış sözleşmesi linkleri. Her biri `/sayfa/slug` formatına yönlendirilmeli (slug’lar yukarıdaki ile tutarlı olmalı).

---

### Grup 5 Özet Kontrol Listesi

- [x] Admin: `PagesController` + `/admin/pages` (liste, create, edit; silme isteğe bağlı).
- [x] `config/routes.php`: `/sayfa/:slug` → `PageController::showBySlug`.
- [x] `PageController::showBySlug()`: slug ile `pages` kaydı getir, 404 veya view.
- [x] View: `frontend/pages/show.php` (veya benzeri) – title, content.
- [x] Seed veya manuel: SSS, İade, KVKK, Mesafeli satış, Hakkımızda için `pages` kayıtları (`database/seeds/seed_pages.php`).
- [x] Footer: SSS, İade, KVKK, Mesafeli satış linkleri (Hakkımızda → `/sayfa/hakkimizda`).
- [x] (Opsiyonel) `/hakkimizda` rotası hâlâ mevcut (sabit view); footer’da Hakkımızda `/sayfa/hakkimizda` ile panelden yönetilen sayfaya gider.

---

## 5. Grup 6 – İsteğe Bağlı / İleri

Aşağıdaki Grup 6 özellikleri **tamamlanmıştır**: Benzer ürünler (5.5), Dashboard grafik (5.2), Anasayfa slider (5.3), Favori listesi (5.4). Kalan: Sipariş onay e-postası (5.1), beden/renk varyant (5.6 – ayrı plan).

### 5.1 Sipariş onay e-postası (A25) + E-posta ayarları (B38) – Henüz yapılmadı

- **Amaç:** Sipariş oluşturulunca müşteriye e-posta gönderimi (sipariş özeti).
- **Gerekli:** SMTP veya PHP mail; ayarlar `settings` tablosunda (örn. `email` grubu: smtp_host, smtp_user, from_email vb.) veya `email_templates` tablosu (schema’da var).
- **Yer:** Sipariş oluşturma sonrası `CheckoutController` (veya Orders model/service) içinde mail gönderim çağrısı.
- **Admin:** Ayarlar sayfasında e-posta bölümü (B38) – SMTP bilgileri, “Sipariş onay e-postası açık/kapalı” vb.

### 5.2 Dashboard grafik (B7) ✅

- **Durum:** Tamamlandı. `DashboardController::index()` son 30 gün günlük satış tutarını çeker; `dashboard.php` içinde bar grafik (HTML/CSS) gösterilir. İptal ve iade hariç.

### 5.3 Anasayfa slider (A1/B33) ✅

- **Durum:** Tamamlandı. `sliders` tablosu kullanılıyor. Admin: `SlidersController` + `/admin/sliders` (liste, create, edit, delete); görsel `uploads/sliders`. Mağaza: `HomeController` sliderları yükler; `home.php` üstte slider (önceki/sonraki, 5 sn otomatik).

### 5.4 Favori listesi (A33) ✅

- **Durum:** Tamamlandı. `wishlists` tablosu kullanılıyor. Rotalar: `/hesabim/favoriler` (liste), `/favori/ekle`, `/favori/sil` (user middleware). Ürün detayda "Favorilere ekle" / "Favorilerden çıkar"; Hesabım → Favorilerim; header'da Favorilerim linki. (Aşağıdaki eski açıklama referans için bırakıldı.)
- **Amaç (referans):** Giriş yapmış üyenin beğendiği ürünleri kaydetme.
- **Veritabanı:** `wishlists` veya `user_favorites` (user_id, product_id, created_at). Schema’da yoksa eklenmeli.
- **Mağaza:** Ürün kartı veya detayda “Favorilere ekle” butonu; `/hesabim/favoriler` gibi bir sayfa.
- **Middleware:** Giriş gerekli (user).

### 5.5 Benzer ürünler (A17) ✅

- **Durum:** Tamamlandı. `ProductController::show()` aynı kategoriden (yoksa tüm ürünlerden) rastgele 4 ürün çeker; `product/show.php` altında "Bunları da beğenebilirsiniz" bölümü.
- **Amaç (referans):** Ürün detay sayfasında “Bunları da beğenebilirsiniz” – aynı kategoriden veya rastgele N ürün.
- **Yer:** `ProductController::show()` – aynı `category_id` veya rastgele ürünler çekilir; view’a `relatedProducts` geçilir.
- **View:** `app/Views/frontend/product/show.php` alt kısmında ürün kartları.

### 5.6 Beden/renk ve varyant (detaylandırmalar)

- **Doküman:** `docs/DETAYLANDIRMALAR_SONRA.md`, `docs/TASARIM_VE_DETAYLANDIRMA_PLANI.md`.
- **Durum:** Veritabanında `attributes`, `attribute_values`, `product_variants`, `product_variant_attribute_values` tabloları hazır; panel ve mağaza tarafında varyant yönetimi ve mağazada beden/renk seçimi **henüz yapılmadı**. Bu iş ayrı bir planlama ile ilerletilmeli.

---

## 6. Dosya Yapısı ve Kritik Yollar

```
lumina-ecommerce/
├── config/
│   ├── bootstrap.php      # env(), autoload
│   └── routes.php         # Tüm rotalar
├── public/
│   ├── index.php          # Tek giriş noktası
│   └── .htaccess          # RewriteRule -> index.php
├── app/
│   ├── Router.php
│   ├── Config/Database.php
│   ├── Helpers/Settings.php
│   ├── Middleware/
│   │   ├── AdminAuth.php
│   │   └── UserAuth.php
│   ├── Controllers/
│   │   ├── Frontend/
│   │   │   ├── HomeController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── ContactController.php
│   │   │   ├── PageController.php      # about(), showBySlug() eklenecek
│   │   │   ├── SearchController.php
│   │   │   ├── UserAuthController.php
│   │   │   └── AccountController.php
│   │   └── Admin/
│   │       ├── AdminBaseController.php
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── CategoriesController.php
│   │       ├── ProductsController.php
│   │       ├── OrdersController.php
│   │       ├── ContactMessagesController.php
│   │       ├── SettingsController.php
│   │       ├── CustomersController.php
│   │       ├── CouponsController.php
│   │       └── ReportsController.php
│   └── Views/
│       ├── frontend/
│       │   ├── layouts/main.php
│       │   ├── home.php
│       │   ├── category/show.php
│       │   ├── product/show.php
│       │   ├── cart/index.php
│       │   ├── checkout/form.php, success.php
│       │   ├── contact/index.php
│       │   ├── pages/about.php         # Sabit Hakkımızda (şu an)
│       │   ├── pages/show.php          # Grup 5: slug ile sayfa (oluşturulacak)
│       │   ├── search/index.php
│       │   ├── auth/login.php, register.php
│       │   └── account/*.php
│       └── admin/
│           ├── layouts/main.php
│           ├── dashboard.php
│           ├── categories/*.php
│           ├── products/*.php
│           ├── orders/*.php
│           ├── contact_messages/*.php
│           ├── settings/index.php
│           ├── customers/*.php
│           ├── coupons/*.php
│           ├── reports/*.php
│           └── pages/                   # Grup 5: index.php, form.php vb. (oluşturulacak)
├── database/
│   ├── schema.sql
│   └── seeds/seed_admin.php
└── docs/
    ├── EKLENMESI_GEREKEN_ISLEVLER.md
    ├── TEST_VE_YONLENDIRME.md
    ├── OZELLIK_LISTESI.md
    ├── TASARIM_VE_DETAYLANDIRMA_PLANI.md
    ├── DETAYLANDIRMALAR_SONRA.md
    ├── MAMP_APACHE_AYARLARI.md
    ├── SCHEMA_NASIL_CALISTIRILIR.md
    └── PROJE_EL_KITABI_VE_KALAN_GRUPLAR.md  # Bu dosya
```

---

## 7. Veritabanı Şeması (İlgili Tablolar)

**pages (Grup 5 için):**

```sql
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`)
);
```

**settings (Grup 1’de kullanılıyor):**

```sql
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) DEFAULT NULL,
  `key` varchar(100) NOT NULL,
  `value` text,
  ...
  UNIQUE KEY `group_key` (`group_name`, `key`)
);
```

- Okuma: `App\Helpers\Settings::getGroup('general')` → ['site_name' => ..., 'contact_email' => ...].
- Yazma: `Settings::set('general', 'site_name', $value)`.

---

## 8. Yeni Chat İçin Kısa Talimat

Yeni açtığınız chat’te şunu yazabilirsiniz:

- **“Grup 5’ten devam et. @docs/PROJE_EL_KITABI_VE_KALAN_GRUPLAR.md dosyasını kullan. Önce sabit sayfalar (admin CRUD + mağaza /sayfa/slug), sonra footer linkleri.”**

Veya:

- **“Grup 6’daki [örn. sipariş onay e-postası / dashboard grafik / slider] özelliğini ekle. @docs/PROJE_EL_KITABI_VE_KALAN_GRUPLAR.md dosyasındaki ilgili bölüme göre yap.”**

Bu el kitabı, proje durumu ve test mantığı (`TEST_VE_YONLENDIRME.md` ile uyumlu) dahil **tüm detayları ve yapılması gereken işleri** tek yerde toplar; yeni chat’te bağlam kaybı olmadan devam edebilirsiniz.

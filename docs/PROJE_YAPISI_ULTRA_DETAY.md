# Lumina E-Commerce – Proje Yapısı (Ultra Detaylı)

Bu doküman, `lumina-ecommerce` klasörünün tam incelemesine dayanarak hazırlanmıştır.

---

## 1. Genel Bakış

| Özellik | Değer |
|--------|--------|
| **Proje adı** | Lumina Boutique – E-Ticaret |
| **Hedef** | Kadın ürünleri satışı; mağaza + yönetim paneli |
| **Dil** | PHP (vanilla, framework yok), MySQL/MariaDB |
| **Frontend** | Tailwind CSS (CDN), Alpine.js, Google Fonts (Cinzel, Inter) |
| **Giriş noktası** | `public/index.php` (tüm istekler buraya yönlendirilir) |
| **Document root** | Mutlaka `public/` olmalı (güvenlik) |

---

## 2. Teknoloji ve Mimari

### 2.1 Mimari Özet

- **MVC benzeri:** Controller’lar iş mantığını yürütür, View’lar HTML üretir. **Model katmanı yok** – `app/Models/` boş; tüm veritabanı erişimi Controller’lar ve Helper’lar içinde **PDO** ile yapılıyor.
- **Router:** Tek dosya (`config/routes.php`) – URI deseni → `[Controller::class, 'method', ['middleware'...]]`. Dinamik parçalar: `:slug`, `:id`.
- **Middleware:** Sadece iki tane: `AdminAuth` (admin oturumu), `UserAuth` (müşteri oturumu). Rota eşleşmesinden sonra sırayla çalıştırılır.
- **Oturum:** PHP `session_start()` bootstrap’ta; admin için `$_SESSION['admin_id']`, müşteri için `$_SESSION['user_id']`.
- **Sepet:** Session tabanlı (`$_SESSION['cart']`). Key formatı: `p{productId}`, `p{productId}_v{variantId}` veya `p{productId}_s_{size}`.

### 2.2 Autoload ve Ortam

- **PSR-4 benzeri:** `App\` → `app/` (bootstrap’ta tek `spl_autoload_register`).
- **Ortam:** `.env` dosyası bootstrap’ta satır satır okunup `$_ENV` ve `putenv` ile yüklenir; `env('KEY', default)` helper’ı kullanılıyor.
- **Veritabanı:** `App\Config\Database` – tek PDO örneği (singleton), `Database::getConnection()`.

---

## 3. Klasör Yapısı (Ultra Detaylı)

```
lumina-ecommerce/
├── .env                    # Ortam değişkenleri (DB, APP_URL, APP_DEBUG, APP_NAME) – Git’e eklenmez
├── .gitignore
├── .gitkeep                # Boş klasörleri Git’te tutmak için
├── README.md               # Proje el kitabı, klasör açıklamaları, manuel adımlar
├── test.php                # Geçici/test dosyası
├── package-lock.json       # Tailwind (devDependency) için
│
├── public/                 # ★ Document root – tarayıcının erişebildiği tek yer
│   ├── .htaccess           # Tüm istekleri index.php’ye yönlendirir; /admin dahil
│   ├── index.php           # Tek giriş noktası: bootstrap → Router → Middleware → Controller
│   ├── admin/              # Boş (.gitkeep) – admin paneli index.php üzerinden /admin rotasıyla
│   ├── assets/
│   │   ├── css/main.css    # Tailwind build çıktısı (minified)
│   │   ├── fonts/          # Boş
│   │   ├── images/         # favicon.svg, lumina-logo, aksesuar.jpg, sale.jpg
│   │   ├── img/            # Boş
│   │   └── js/             # Boş
│   └── uploads/            # Ürün/kategori yüklemeleri (products/, categories/ altında olacak)
│
├── config/                 # Uygulama genel yapılandırma
│   ├── bootstrap.php       # BASE_PATH, session_start, .env yükleme, env(), autoload
│   └── routes.php          # Rota listesi: URI => [Controller, method, middlewares]
│
├── app/
│   ├── Config/
│   │   └── Database.php    # PDO singleton, env(DB_*) ile bağlantı
│   │
│   ├── Controllers/
│   │   ├── Frontend/       # Mağaza tarafı
│   │   │   ├── HomeController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── ContactController.php
│   │   │   ├── PageController.php      # Hakkımızda, SSS, /sayfa/:slug
│   │   │   ├── SearchController.php
│   │   │   ├── UserAuthController.php  # Kayıt, giriş, çıkış, şifremi unuttum, şifre sıfırla
│   │   │   └── AccountController.php   # Hesabım, siparişler, adresler, profil, favoriler
│   │   └── Admin/
│   │       ├── AdminBaseController.php # baseUrl(), render(), renderWithoutLayout()
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── CategoriesController.php
│   │       ├── ProductsController.php
│   │       ├── OrdersController.php
│   │       ├── ContactMessagesController.php
│   │       ├── SettingsController.php
│   │       ├── CustomersController.php
│   │       ├── CouponsController.php
│   │       ├── ReportsController.php
│   │       ├── PagesController.php
│   │       ├── SlidersController.php
│   │       └── AttributesController.php
│   │
│   ├── Helpers/
│   │   ├── env.php         # (Kullanılmıyor gibi; env() bootstrap’ta tanımlı)
│   │   └── Settings.php    # settings tablosu: get/set/getGroup, cache’li
│   │
│   ├── Middleware/
│   │   ├── AdminAuth.php   # $_SESSION['admin_id'] yoksa → /admin/login
│   │   └── UserAuth.php    # $_SESSION['user_id'] yoksa → /giris (veya ilgili sayfa)
│   │
│   ├── Models/             # ★ BOŞ – sadece .gitkeep (veri erişimi Controller/Helper’da PDO)
│   │
│   ├── Router.php          # App\Router – URI + :slug/:id eşleştirme, $_GET['_slug']/_id atar
│   │
│   └── Views/
│       ├── frontend/       # Mağaza şablonları
│       │   ├── layouts/main.php   # Ortak layout: header/footer/cart-drawer/toast includes’dan
│       │   ├── home.php
│       │   ├── category/show.php
│       │   ├── product/show.php
│       │   ├── cart/index.php
│       │   ├── checkout/checkout.php, success.php (form.php kullanılmıyor)
│       │   ├── contact/index.php, contact.php
│       │   ├── pages/about.php, faq.php, show.php, policy-detail.php
│       │   ├── search/index.php
│       │   ├── auth/login.php, register.php, forgot-password.php, reset-password.php
│       │   └── account/index.php, orders.php, order_show.php, addresses.php, address_form.php, address_delete.php, profile.php, favoriler.php
│       └── admin/          # Panel şablonları
│           ├── layouts/main.php
│           ├── login.php, dashboard.php
│           ├── categories/, products/, orders/, contact_messages/, settings/
│           ├── customers/, coupons/, reports/, pages/, sliders/, attributes/
│           └── partials/
│
├── includes/               # Frontend layout’un kullandığı ortak parçalar (header, footer, sepet çekmecesi, toast)
│   ├── functions.php       # getLuminaImage() – Unsplash demo görselleri (hero/product/sale)
│   ├── layout.php          # Alternatif layout (layout.php ile açılan sayfalar için)
│   ├── header.php          # Menü (kategoriler DB’den), logo, arama, sepet, giriş/hesabım
│   ├── footer.php
│   ├── hero.php
│   ├── home-categories.php
│   ├── featured-products.php
│   ├── features-bar.php
│   ├── cart-drawer.php
│   └── toast.php
│
├── database/
│   ├── schema.sql          # Tüm tabloların CREATE TABLE (tek seferde çalıştırılır)
│   ├── migrations/         # Boş (.gitkeep) – migration dosyası yok
│   └── seeds/
│       ├── seed_admin.php       # İlk admin rolü + admin@luminaboutique.com / Admin123!
│       ├── seed_pages.php       # Sabit sayfalar (Hakkımızda, İletişim, SSS, politikalar vb.)
│       ├── seed_attributes.php  # Beden/renk attribute’ları
│       └── insert_missing_pages.sql
│
├── storage/                # Çalışma çıktıları (Git’te sadece .gitkeep)
│   ├── cache/
│   ├── logs/
│   ├── sessions/
│   └── backups/
│
├── routes/                 # Boş (.gitkeep) – rotalar config/routes.php’de
│
├── tests/
│   ├── Unit/
│   └── Feature/            # Boş – PHPUnit vb. henüz yok
│
└── docs/                   # Dokümantasyon
    ├── FRONTEND_SAYFA_DURUMU.md
    ├── OZELLIK_LISTESI.md
    ├── PROJE_EL_KITABI_VE_KALAN_GRUPLAR.md
    ├── MAMP_VERITABANI_KURULUM.md, MAMP_APACHE_AYARLARI.md
    ├── SCHEMA_NASIL_CALISTIRILIR.md
    ├── ODEME_SAYFASI_EKSIKLER_VE_EKLENENLER.md
    ├── E-POSTA_GONDERIMI_YAPILACAKLAR.md, EKLENMESI_GEREKEN_ISLEVLER.md
    ├── TASARIM_ASAMASI_REHBERI.md, TASARIM_VE_DETAYLANDIRMA_PLANI.md
    ├── DETAYLANDIRMALAR_SONRA.md
    └── TEST_VE_YONLENDIRME.md
```

---

## 4. İstek Akışı

1. İstek `public/` altına gelir (document root).
2. `.htaccess`: Mevcut dosya/klasör yoksa (ve `/admin` dahil) istek `index.php`’ye yönlendirilir.
3. `index.php`:  
   - `config/bootstrap.php` require edilir (BASE_PATH, session, .env, autoload).  
   - `Router` ile `config/routes.php` kullanılarak URI eşleştirilir.  
   - Eşleşme yoksa 404 HTML çıktılanır ve çıkılır.  
   - Eşleşme varsa: `[$controllerClass, $method, $middlewares]` alınır.  
   - Middleware’ler çalıştırılır (`admin` → AdminAuth, `user` → UserAuth).  
   - `$controller = new $controllerClass(); $controller->$method();` çağrılır.
4. Controller: PDO ile veri çeker, `render('frontend/...', $data)` veya `renderWithIncludesLayout(...)` ile view’a veri gönderir.
5. View: `app/Views/...` içinde bir PHP dosyası; çoğu kez `app/Views/frontend/layouts/main.php` layout’unu kullanır. Layout ise `includes/header.php`, `includes/footer.php`, `includes/cart-drawer.php`, `includes/toast.php` require eder.

---

## 5. Rota Özeti

| Grup | Örnek | Controller | Middleware |
|------|--------|------------|------------|
| Mağaza | `/`, `/kategori/:slug`, `/urun/:slug`, `/sepet`, `/odeme`, `/iletisim`, `/hakkimizda`, `/sss`, `/sayfa/:slug`, `/arama` | Frontend | - |
| Üye | `/kayit`, `/giris`, `/cikis`, `/sifremi-unuttum`, `/sifre-sifirla` | UserAuthController | - |
| Hesabım | `/hesabim`, `/hesabim/siparisler`, `/hesabim/adresler`, `/hesabim/favoriler`, `/favori/ekle`, `/favori/sil` | AccountController | user |
| Admin | `/admin`, `/admin/login`, `/admin/categories`, `/admin/products`, … | Admin\* | admin (login hariç) |

Sepet AJAX/redirect: `/sepet/ekle`, `/sepet/guncelle`, `/sepet/sil`, `/sepet/cekmece`.

---

## 6. Veritabanı (schema.sql Özeti)

- **locales** – Çoklu dil (A6).
- **users**, **password_reset_tokens** – Müşteri ve şifre sıfırlama.
- **admin_roles**, **admin_permissions**, **admin_role_permissions**, **admin_users**, **admin_password_reset_tokens** – Panel yetkileri.
- **brands** – Marka (isteğe bağlı).
- **categories**, **category_translations** – Hiyerarşik kategori.
- **attributes**, **attribute_values** – Beden/renk vb.
- **products**, **product_translations**, **product_images**, **product_variants**, **product_variant_attribute_values** – Ürün ve varyant.
- **addresses** – Müşteri adresleri.
- **coupons** – Kupon (yüzde/sabit, min tutar, kullanım sayısı).
- **orders**, **order_items**, **order_status_history**, **shipments**, **order_returns** – Sipariş ve kargo.
- **cart_items** – Giriş yapmış kullanıcı için kalıcı sepet (session sepeti ayrı).
- **wishlists** – Favoriler.
- **pages**, **page_translations** – Sabit sayfalar.
- **faqs** – SSS.
- **sliders** – Anasayfa slider.
- **posts** – Blog (isteğe bağlı).
- **shipping_methods** – Kargo yöntemleri.
- **settings** – Key-value site ayarları (Stripe, SMTP vb.).
- **email_templates** – E-posta şablonları.
- **contact_messages** – İletişim formu mesajları.

---

## 7. Frontend vs Admin – Render Farkları

- **Admin:** Tüm controller’lar `AdminBaseController`’ı extend eder; `$this->render('admin/...', $data)` ve `renderWithoutLayout()` kullanır. Layout: `app/Views/admin/layouts/main.php`.
- **Frontend:** Ortak bir base controller yok. Her controller kendi `baseUrl()` ve `render()` metoduna sahip (kod tekrarı var).
  - Çoğu sayfa: view’ı `app/Views/frontend/...` içinde açar, sonra `app/Views/frontend/layouts/main.php` layout’unu kullanır. Layout, `includes/header.php`, `footer.php`, `cart-drawer.php`, `toast.php` require eder.
  - **Contact** ve **Sepet** sayfaları: `renderWithIncludesLayout()` kullanır – view çıktısı `includes/layout.php` ile sarılır (Tailwind + Alpine ile aynı stil).

---

## 8. Önemli Notlar ve Tutarsızlıklar

1. **Model yok:** Veritabanı sorguları Controller ve Helper içinde; ileride Model sınıfları eklenebilir.
2. **Migrations boş:** Şema tek dosyada (`schema.sql`); migration dosyası yok.
3. **Frontend base controller yok:** `baseUrl()` ve `render()` her Frontend controller’da tekrarlanıyor; bir `FrontendBaseController` ile toplanabilir.
4. **checkout/form.php:** Dokümana göre kullanılmıyor; CheckoutController sadece `checkout/checkout.php` kullanıyor.
5. **account/address_form.php ve address_delete.php:** Eski/inline stil; ana akış hesabım sayfasında modal ile yapılıyor olabilir.
6. **includes/layout.php:** Sadece `renderWithIncludesLayout` kullanan sayfalar (iletişim, sepet) için kullanılıyor; diğer sayfalar `app/Views/frontend/layouts/main.php` kullanıyor.
7. **Tailwind:** CDN + `main.css` (build) birlikte var; proje kökünde `tailwind.config` veya kaynak CSS yoksa `main.css` muhtemelen ayrı bir build ile üretilmiş.
8. **Demo görseller:** `includes/functions.php` içinde `getLuminaImage('hero'|'product'|'sale', index)` Unsplash URL’leri döndürüyor; yerel görseller `public/assets/images/` ve `uploads/` kullanılıyor.

---

## 9. Özet Tablo

| Bileşen | Konum | Not |
|---------|--------|-----|
| Giriş noktası | public/index.php | Bootstrap → Router → Middleware → Controller |
| Rotalar | config/routes.php | Tek dosya, pattern => [Controller, method, middleware] |
| Router | app/Router.php | :slug, :id → $_GET['_slug'], $_GET['_id'] |
| Veritabanı | app/Config/Database.php | PDO singleton |
| Ayarlar | app/Helpers/Settings.php | settings tablosu, cache’li |
| Frontend layout | app/Views/frontend/layouts/main.php | includes (header, footer, cart-drawer, toast) |
| Admin layout | app/Views/admin/layouts/main.php | Panel ortak şablon |
| Ortak UI parçaları | includes/ | header, footer, hero, cart-drawer, toast, functions (getLuminaImage) |
| Şema | database/schema.sql | Tüm tablolar |
| Seed’ler | database/seeds/ | seed_admin, seed_pages, seed_attributes, insert_missing_pages.sql |

Bu doküman proje yapısının ultra detaylı özetidir; geliştirme ve refactoring planı için referans olarak kullanılabilir.

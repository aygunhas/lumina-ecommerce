# Lumina Boutique – E-Ticaret Projesi

Bu proje **Lumina Boutique** için PHP ile yazılacak bir e-ticaret sitesi ve yönetim panelinin klasör yapısıdır. Kadın ürünleri satışı hedeflenmektedir.

Aşağıda her klasörün ne işe yaradığı ve projenin her adımında **manuel yapmanız gerekenler** anlaşılır dille açıklanmaktadır.

---

## Klasör Yapısı ve Açıklamaları

### `public/` – Dışarıya Açık Tek Klasör

**Ne işe yarar?**  
Web sitesine gelen herkes sadece bu klasörün içindekilere erişebilir. Tüm PHP giriş noktası (örneğin `index.php`) ve müşterinin gördüğü resim, CSS, JS dosyaları burada olur. Güvenlik için **tüm diğer klasörler** (app, config, database vb.) tarayıcıdan doğrudan açılamaz; sadece sunucu içinden kullanılır.

- **`public/admin/`** – Yönetim paneline giriş adresi buradan verilir (örn. `site.com/admin`). Panel sayfaları ve panel için özel CSS/JS burada veya buradan çağrılır.
- **`public/assets/`** – Mağaza tarafının görünümü için:
  - **css/** – Stil dosyaları
  - **js/** – JavaScript dosyaları
  - **images/** – Logo, ikon, genel görseller
  - **fonts/** – Özel fontlar
- **`public/uploads/`** – Yüklenen dosyalar:
  - **products/** – Ürün fotoğrafları
  - **categories/** – Kategori görselleri  

**Manuel:** Sunucuyu kurarken “document root”u mutlaka `public` klasörüne ayarlayın. Böylece kimse `app/` veya `config/` gibi klasörlere tarayıcıdan ulaşamaz.

---

### `app/` – Uygulama Mantığı (Kodlar Burada)

**Ne işe yarar?**  
Sitenin nasıl çalışacağını belirleyen PHP dosyaları burada toplanır. Tarayıcı bu klasöre doğrudan erişemez; sadece `public` üzerinden çalışan dosyalar buradakileri kullanır.

- **`app/Controllers/`** – Gelen isteklere göre “ne yapılacak” kararı veren sınıflar:
  - **Frontend/** – Mağaza sayfaları (anasayfa, ürün listesi, sepet, ödeme vb.)
  - **Admin/** – Yönetim paneli işlemleri (ürün ekleme, sipariş listesi, kullanıcı yönetimi vb.)
- **`app/Models/`** – Veritabanı tablolarıyla konuşan sınıflar (ürün, sipariş, müşteri vb.).
- **`app/Views/`** – Ekrana basılacak HTML şablonları:
  - **frontend/** – Müşteri tarafı sayfaları; **layouts** (ortak üst/alt), **partials** (tekrar kullanılan parçalar).
  - **admin/** – Panel sayfaları; yine **layouts** ve **partials**.
- **`app/Config/`** – Veritabanı bağlantısı, site ayarları gibi yapılandırma sınıfları/dosyaları.
- **`app/Helpers/`** – Sık kullanılan küçük fonksiyonlar (tarih formatı, fiyat yazdırma vb.).
- **`app/Middleware/`** – İstek gelmeden önce/sonra çalışan kontroller (giriş yapmış mı, yetkisi var mı vb.).

**Manuel:** Kod yazmaya başladığınızda tüm iş mantığını `app/` altında tutun; `public/` içinde mümkün olduğunca sadece tek giriş noktası (index.php) ve statik dosyalar olsun.

---

### `config/` – Genel Yapılandırma

**Ne işe yarar?**  
Uygulama genelinde kullanılan ayarlar (veritabanı, e-posta sunucusu, ödeme API bilgileri vb.) burada tutulur. Bu ayarlar `.env` dosyasından okunabilir; böylece şifreler kod içine yazılmaz.

**Manuel:** Hassas bilgileri (DB şifresi, API anahtarları) asla bu klasördeki dosyalara doğrudan yazmayın; `.env` kullanın ve `.env` dosyasını Git’e eklemeyin.

---

### `database/` – Veritabanı Yapısı ve Örnek Veriler

**Ne işe yarar?**  
Veritabanı tablolarının nasıl oluşturulacağı ve isteğe bağlı örnek veriler burada tanımlanır.

- **`migrations/`** – Tabloları oluşturan/değiştiren adım adım dosyalar (örn. “ürünler tablosu”, “siparişler tablosu”).
- **`seeds/`** – İlk kurulumda veya test için eklenecek örnek veriler (demo ürünler, admin kullanıcısı vb.).

**Manuel:** Canlı veritabanında migration çalıştırmadan önce mutlaka yedek alın. Seed’leri sadece geliştirme/test ortamında kullanın; canlıda dikkatli kullanın.

---

### `storage/` – Sunucuda Oluşan Geçici ve Kalıcı Dosyalar

**Ne işe yarar?**  
Uygulama çalışırken oluşan dosyalar burada tutulur; bunlar “kod” değil, çalışma çıktılarıdır.

- **`cache/`** – Hız için saklanan geçici veriler.
- **`logs/`** – Hata ve işlem logları (kim ne zaman giriş yaptı, hangi hata oluştu vb.).
- **`sessions/`** – Oturum bilgileri (giriş yapmış kullanıcı bilgisi vb.) dosya tabanlı kullanılacaksa burada.
- **`backups/`** – Veritabanı veya dosya yedekleri (manuel veya zamanlanmış).

**Manuel:** Bu klasörlerin yazılabilir (writable) olduğundan emin olun. Sunucuda `chmod` veya dosya sahipliği ile web sunucusunun yazmasına izin verin. `storage/` içeriği Git’e eklenmemeli; sadece boş klasör yapısı (ör. `.gitkeep`) takip edilir.

---

### `routes/` – Adres Yönlendirmeleri

**Ne işe yarar?**  
“Şu URL’e gelindiğinde hangi Controller çalışsın?” bilgisi burada tanımlanır (örn. `/urunler` → Ürün listesi, `/admin/giris` → Admin giriş sayfası).

**Manuel:** Yeni bir sayfa veya link eklediğinizde ilgili route’u burada tanımlayın; böylece yapı düzenli kalır.

---

### `tests/` – Otomatik Testler

**Ne işe yarar?**  
Kodun doğru çalışıp çalışmadığını otomatik kontrol eden test dosyaları (ileride PHPUnit vb. ile).

- **`Unit/`** – Tek fonksiyon/sınıf testleri.
- **`Feature/`** – Sayfa veya akış testleri (örn. “sepete ekle” butonu çalışıyor mu).

**Manuel:** Kod yazmaya başladıktan sonra önemli işlemler için test yazmak, ileride hata yapmanızı azaltır. Şu an sadece klasör hazır.

---

## Proje Adımlarında Manuel Yapılacaklar

### 1. Ortam Hazırlığı

- Bilgisayarınızda **PHP** (örn. 7.4 veya 8.x) ve **MySQL/MariaDB** (veya kullandığınız veritabanı) kurulu olsun.
- İsterseniz **XAMPP**, **Laragon**, **MAMP** gibi paketler tek tıkla PHP + MySQL + web sunucusu verir.
- **Composer** kurulu olsun (PHP için paket yöneticisi). İleride kütüphane eklemek için gerekir.

**Manuel:**  
- `.env.example` dosyasını kopyalayıp adını **`.env`** yapın.  
- `.env` içinde veritabanı adı, kullanıcı adı, şifre ve `APP_URL` gibi değerleri kendi ortamınıza göre doldurun.  
- `.env` dosyasını asla Git’e eklemeyin (zaten `.gitignore`’da olmalı).

---

### 2. Veritabanı

- MySQL/MariaDB’de “Lumina Boutique” için bir veritabanı oluşturun (örn. `lumina_db`).
- Bu veritabanı adını, kullanıcıyı ve şifreyi `.env` dosyasına yazın.

**Manuel:**  
- Migration’lar yazıldıktan sonra, migration’ları çalıştıran komutu (projede tanımlanacak) bir kez çalıştırarak tabloları oluşturun.  
- İlk admin kullanıcısı için seed kullanılacaksa, seed komutunu da proje dokümantasyonunda belirtilen şekilde çalıştırın.

---

### 3. Web Sunucusu Ayarı

- Yerel geliştirme: Proje klasörünü açın, **document root** olarak sadece **`public`** klasörünü işaret edin.  
  Örnek: `http://localhost/lumina-ecommerce` adresi `lumina-ecommerce/public` içeriğini göstermeli; `app` veya `config` doğrudan açılmamalı.
- Canlı sunucuda: Domain’in kökü yine `public` klasörü olacak şekilde ayarlanmalı.

**Manuel:**  
- Sunucu panelinden (cPanel, Plesk vb.) veya sunucu yapılandırma dosyasından (Apache/Nginx) document root’u `public` yapın.  
- `public` dışındaki klasörlere tarayıcıdan erişim engelli olmalı.

---

### 4. Giriş Noktası (İleride Kod Yazılırken)

- Tüm istekler tek noktadan girmeli: `public/index.php` (ve gerekirse `public/admin/index.php` veya tek index’te `/admin` ayrımı).
- Bu dosya, `routes` ve `app` klasörlerini kullanarak doğru Controller’a yönlendirir.

**Manuel:**  
- Kod yazılmaya başlandığında, tarayıcıda sadece `index.php` (veya temiz URL ile) kullanın; `app/` veya `config/` içindeki dosyalara doğrudan link verilmemeli.

---

### 5. Yüklenen Dosyalar (uploads)

- Ürün ve kategori resimleri `public/uploads/products` ve `public/uploads/categories` altına yazılacak.
- Bu klasörlerin web sunucusu kullanıcısı tarafından **yazılabilir** olması gerekir.

**Manuel:**  
- Sunucuda `public/uploads` (ve alt klasörleri) için yazma izni verin (chmod 755 veya 775, kullandığınız sunucuya göre).  
- Canlıda yedek alırken `uploads` klasörünü de yedekleyin; ürün resimleri buradadır.

---

### 6. Güvenlik (Genel)

- **.env** içinde şifre ve API anahtarları tutulur; bu dosya asla Git’e eklenmez ve paylaşılmaz.
- Müşteri tarafı ile admin paneli ayrı düşünülür; admin sayfaları giriş ve yetki kontrolü ile korunmalı.
- Veritabanı sorgularında mutlaka “prepared statement” kullanın (SQL enjeksiyonuna karşı).  
  (Bunlar kod yazılırken uygulanacak kurallardır; şu an sadece bilgi.)

**Manuel:**  
- Sunucuya FTP veya SSH ile bağlanan şifreleri güçlü tutun.  
- SSL (HTTPS) kullanın; özellikle giriş ve ödeme sayfalarında zorunludur.

---

## Özet: Klasör Ağacı

```
lumina-ecommerce/
├── public/              → Tarayıcının erişebildiği tek yer (index, assets, admin, uploads)
├── app/                 → Tüm uygulama kodu (Controllers, Models, Views, Config, Helpers, Middleware)
├── config/              → Genel yapılandırma
├── database/            → migrations, seeds
├── storage/             → cache, logs, sessions, backups
├── routes/              → URL → Controller eşlemesi
├── tests/               → Unit ve Feature testleri
├── .env.example         → Örnek ortam dosyası (kopyala → .env yap, doldur)
└── .gitignore           → Git'in takip etmeyeceği dosya/klasörler
```

Bu yapı, PHP e-ticaret projelerinde yaygın ve sağlıklı bir düzen için uygundur. Kod yazmaya geçtiğinizde her şeyi bu klasörlere göre yerleştirirseniz proje okunabilir ve güvenli kalır.

**Lumina Boutique** için bir sonraki adım: `public/index.php` ve `routes` tanımlarını yazmak, ardından veritabanı tablolarını (migrations) ve ilk admin girişini planlamak olacaktır.

# schema.sql Nasıl Çalıştırılır?

`database/schema.sql` dosyası, Lumina Boutique için gerekli tüm tabloları oluşturur. Önce MAMP’te `lumina_db` veritabanını oluşturmuş olmalısınız.

---

## Yöntem 1: phpMyAdmin ile

1. MAMP’i çalıştırın, **phpMyAdmin**’i açın (örn. http://localhost:8888/phpMyAdmin/).
2. Sol taraftan **`lumina_db`** veritabanını seçin.
3. Üst menüden **“SQL”** / **“İçe Aktar”** sekmesine tıklayın.
4. **“Dosya seç”** ile proje klasöründeki **`database/schema.sql`** dosyasını seçin.
5. **“Git”** / **“Go”** butonuna basın.
6. Hata yoksa “X sorgu başarıyla çalıştırıldı” benzeri bir mesaj görürsünüz. Sol tarafta tablolar listelenir.

**Alternatif (SQL sekmesi):**  
- **“SQL”** sekmesine tıklayın.  
- `schema.sql` dosyasının içeriğini kopyalayıp büyük metin kutusuna yapıştırın.  
- **“Git”** deyin.

---

## Yöntem 2: Terminal (mysql komutu) ile

Terminal’de proje klasörüne gidin ve:

```bash
mysql -u root -p lumina_db < database/schema.sql
```

Şifre sorulur; MAMP’te genelde `root` veya boş. Port 8889 kullanıyorsanız:

```bash
mysql -u root -proot -h 127.0.0.1 -P 8889 lumina_db < database/schema.sql
```

Başarılı olursa hiç çıktı vermeden işlem biter. phpMyAdmin’den `lumina_db` seçip tabloları kontrol edebilirsiniz.

---

## Çalıştırdıktan Sonra

- Veritabanında **locales**, **users**, **admin_users**, **categories**, **products**, **orders** vb. tablolar görünür.
- Henüz **veri yoktur**; sadece yapı oluşturulmuştur. İlk admin kullanıcısı ve ayarlar için ileride **seed** dosyaları veya panel üzerinden kurulum kullanılacaktır.

Bu adımdan sonra **ilk admin kullanıcısını** oluşturmak için:

---

## İlk admin kullanıcısı (seed)

**MAMP kullanıyorsanız (Mac):** Terminal’de `php` komutu bulunamaz; MAMP’in PHP’sini tam yoluyla kullanın. Proje kök dizininde:

```bash
/Applications/MAMP/bin/php/php8.4.17/bin/php database/seeds/seed_admin.php
```

MAMP’te farklı PHP sürümü seçiliyse klasör adı değişir (örn. `php8.3.30`, `php7.4.33`). MAMP → Preferences → Web & PHP bölümünden hangi sürümün kullanıldığını görebilirsiniz; aynı adı `bin/php` öncesinde kullanın.

**PHP zaten PATH’teyse** (XAMPP, Laragon veya sistem PHP):

```bash
php database/seeds/seed_admin.php
```

Bu komut:

- `admin_roles` tablosuna **Süper Admin** rolünü ekler (yoksa).
- `admin_users` tablosuna bir admin kullanıcı ekler:
  - **E-posta:** `admin@luminaboutique.com`
  - **Şifre:** `Admin123!`

Tarayıcıda **http://localhost:8888/admin/login** (veya kendi MAMP adresiniz) adresine gidip bu bilgilerle giriş yapabilirsiniz. İlk girişten sonra şifrenizi panelden değiştirin.

---

## Sabit sayfalar (isteğe bağlı seed)

SSS, İade koşulları, KVKK, Mesafeli satış sözleşmesi ve Hakkımızda için örnek sayfa kayıtları eklemek isterseniz:

```bash
php database/seeds/seed_pages.php
```

(MAMP’te: `/Applications/MAMP/bin/php/php8.4.17/bin/php database/seeds/seed_pages.php`)

Bu komut `pages` tablosuna slug’lar: `hakkimizda`, `sss`, `iade-kosullari`, `kvkk`, `mesafeli-satis-sozlesmesi` ile placeholder içerik ekler. Mağaza footer’daki linkler bu sayfalara gider; içerik panelden (**Admin → Sayfalar**) düzenlenebilir.

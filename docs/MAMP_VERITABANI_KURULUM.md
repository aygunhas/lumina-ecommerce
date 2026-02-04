# MAMP’te Veritabanı Oluşturma – Adım Adım

Bu rehber, MAMP kullanarak Lumina Boutique projesi için MySQL veritabanını nasıl oluşturacağınızı anlatır.

---

## 1. MAMP’i çalıştırın

1. **MAMP** uygulamasını açın.
2. **Start Servers** (veya **Start**) butonuna tıklayın.
3. Apache ve MySQL yeşil ışık yanana kadar bekleyin.

---

## 2. phpMyAdmin’i açın

1. Tarayıcıda şu adresi açın:  
   **http://localhost:8888/phpMyAdmin/**  
   (MAMP varsayılan port 8888 ise. Port 80 kullanıyorsanız: **http://localhost/phpMyAdmin/**)
2. Giriş ekranı gelirse:
   - **Kullanıcı adı:** `root`
   - **Şifre:** MAMP’te varsayılan genelde `root` veya **boş** (hiçbir şey yazmayın).  
   Deneyin: önce `root`, olmazsa şifreyi boş bırakın.
3. **Giriş** (Go / Log in) ile panele girin.

---

## 3. Yeni veritabanı oluşturun

1. Sol menüde **“Yeni”** / **“New”** (veya **“Databases”** sekmesi) tıklayın.
2. **“Veritabanı adı”** / **“Database name”** kutusuna şunu yazın:  
   **`lumina_db`**
3. **Karakter seti** (Collation) için:  
   **`utf8mb4_unicode_ci`** veya **`utf8mb4_general_ci`** seçin.  
   (Türkçe karakter ve emoji için uygundur.)
4. **Oluştur** / **Create** butonuna tıklayın.
5. Sol tarafta **`lumina_db`** görünüyorsa veritabanı hazır.

---

## 4. .env dosyasını doldurun

Proje klasöründeki **`.env`** dosyasını açın ve veritabanı satırlarını aşağıdaki gibi yapın.

**MAMP’te varsayılan kullanıcı genelde `root`, şifre `root` veya boş:**

```env
DB_HOST=localhost
DB_NAME=lumina_db
DB_USER=root
DB_PASSWORD=root
```

Şifre boşsa:

```env
DB_PASSWORD=
```

**Not:** MAMP port 8888 kullanıyorsa MySQL portu da 8889 olabilir. O zaman:

```env
DB_HOST=localhost:8889
DB_NAME=lumina_db
DB_USER=root
DB_PASSWORD=root
```

(Proje kodunda bağlantıda port ayrı da okunabilir; gerekirse ileride `DB_PORT=8889` ekleriz.)

---

## 5. Bağlantıyı test etmek (isteğe bağlı)

- phpMyAdmin’de sol taraftan **`lumina_db`** seçin.
- Üstte **“SQL”** sekmesine tıklayın.
- Şunu yazıp **Çalıştır** / **Go** deyin:

```sql
SELECT 1;
```

Hata vermezse MySQL çalışıyor demektir. Proje kodunu yazdığımızda tabloları bu veritabanında oluşturacağız; şimdilik boş veritabanı yeterli.

---

## Özet

| Adım | Ne yaptınız? |
|------|----------------|
| 1 | MAMP’te Apache + MySQL’i başlattınız. |
| 2 | http://localhost:8888/phpMyAdmin (veya kullandığınız port) ile giriş yaptınız. |
| 3 | `lumina_db` adında veritabanı oluşturdunuz, karakter seti utf8mb4. |
| 4 | `.env` dosyasında `DB_NAME=lumina_db`, `DB_USER=root`, `DB_PASSWORD=root` (veya boş) yazdınız. |

Bunları yaptıktan sonra “veritabanı hazır” deyip projeye devam edebiliriz. Ödeme tarafında **Stripe** kullanacağız; entegrasyonu proje ilerledikçe ekleyeceğiz.

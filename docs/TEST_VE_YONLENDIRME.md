# Lumina Boutique – Test ve Yönlendirme Rehberi

Bu dosya, projede **şu an ne olduğunu**, **nasıl test edeceğinizi** ve **sırada neyin geleceğini** anlatır. Her yeni özellik eklendiğinde bu rehber güncellenir; böylece ne yaptığımızı takip edebilir ve elle test edebilirsiniz.

---

## Şu an ne var? (Özet)

| Nerede | Ne var? | Nasıl açılır? |
|--------|---------|----------------|
| **Mağaza** | Anasayfa: slider (varsa), kategoriler + öne çıkan ürünler | Tarayıcıda site adresini açın |
| **Mağaza** | Kategori sayfası: o kategorideki ürünler | Anasayfadan kategori linki veya `/kategori/slug` |
| **Mağaza** | Ürün detay sayfası (görsel, Sepete ekle, Favorilere ekle, Benzer ürünler) | Kategori/anasayfadan ürün linki veya `/urun/slug` |
| **Mağaza** | Sepet: liste, güncelle, kaldır, ödemeye geç | `/sepet` (header’da Sepet linki) |
| **Mağaza** | Ödeme formu ve sipariş oluşturma | `/odeme` (kapıda ödeme, havale, Stripe seçenekleri) |
| **Mağaza** | Sipariş tamamlandı sayfası | `/odeme/tamamlandi` (sipariş numarası gösterilir) |
| **Mağaza** | İletişim sayfası (form + bilgiler) | Footer’daki **İletişim** veya `/iletisim` |
| **Mağaza** | Hakkımızda sayfası | Footer’daki **Hakkımızda** → `/sayfa/hakkimizda` (veya `/hakkimizda`) |
| **Mağaza** | Sabit sayfalar (SSS, İade, KVKK, Mesafeli satış) | `/sayfa/sss`, `/sayfa/iade-kosullari`, `/sayfa/kvkk`, `/sayfa/mesafeli-satis-sozlesmesi`; footer linkleri |
| **Mağaza** | Üye kayıt, giriş, çıkış | Header’da **Kayıt** / **Giriş**; giriş sonrası **Hesabım**, **Çıkış** |
| **Mağaza** | Hesabım: Siparişlerim, Adreslerim, Bilgilerim, Favorilerim | `/hesabim` (giriş gerekli); header’da Favorilerim linki |
| **Mağaza** | Ödeme: kayıtlı adres seçimi (üye), kupon kodu, siparişe user_id ve coupon_id yazılır | `/odeme` |
| **Admin** | Giriş sayfası | `/admin/login` |
| **Admin** | Giriş sonrası: sol menü + kontrol paneli (özet kartlar, son 30 gün satış grafiği, düşük stok uyarısı, son siparişler) | `/admin` |
| **Admin** | Kategoriler: liste, yeni, düzenleme, silme (onaylı) | Sol menü → Kategoriler veya `/admin/categories` |
| **Admin** | Ürünler: liste (arama/filtre: ad, SKU, kategori, stok durumu), yeni ürün, düzenleme, görsel yükleme/silme | Sol menü → Ürünler; filtrele; düzenlemede mevcut görseli "Kaldır" ile silebilirsiniz |
| **Admin** | Siparişler: liste (arama/filtre), detay, **Yazdır** (fiş), durum güncelleme, kargo takip no | Sol menü → Siparişler; filtre (sipariş no, müşteri, e-posta, durum, tarih); detayda **Yazdır** ile fiş sayfası |
| **Admin** | Müşteriler: liste (ad, e-posta, telefon ile arama), detay (sipariş geçmişi, adresler) | Sol menü → Müşteriler veya `/admin/customers` |
| **Admin** | Kuponlar: liste, yeni kupon, düzenleme, silme (kod, tip, değer, min. sepet, geçerlilik, kullanım) | Sol menü → Kuponlar veya `/admin/coupons` |
| **Admin** | Raporlar: Satış raporu (tarih aralığı, satış tutarı, en çok satan ürünler), Stok raporu (stok listesi, düşük stok) | Sol menü → Raporlar veya `/admin/reports` |
| **Admin** | İletişim mesajları: liste ve detay (okundu işaretleme) | Sol menü → İletişim mesajları veya `/admin/contact-messages` |
| **Admin** | Sayfalar: liste, yeni sayfa, düzenleme, silme (slug, başlık, içerik, meta, sıra, aktif) | Sol menü → Sayfalar veya `/admin/pages` |
| **Admin** | Slider: liste, yeni slider, düzenleme, silme (görsel, başlık, link); anasayfada gösterim | Sol menü → Slider veya `/admin/sliders` |
| **Veritabanı** | Tablolar hazır; henüz kategori/ürün verisi yok | phpMyAdmin ile bakabilirsiniz |

---

## Adım adım test

### 1. Mağaza anasayfayı test etmek

1. MAMP’in çalıştığından emin olun (Apache + MySQL yeşil).
2. Tarayıcıda açın: **http://localhost:8888/**  
   (Port farklıysa kendi adresinizi kullanın.)
3. **Görmeniz gereken:** "Lumina Boutique" başlığı ve "Hoş geldiniz. Mağaza sayfaları yakında eklenecek." metni.
4. **Bu ne demek?** Tüm istekler `public/index.php` üzerinden geliyor; router `/` adresini `HomeController`’a veriyor; controller da `app/Views/frontend/home.php` şablonunu gösteriyor. Yani **mağaza tarafı çalışıyor**.

---

### 2. Admin girişini test etmek

1. Tarayıcıda açın: **http://localhost:8888/admin/login**
2. **Görmeniz gereken:** "Lumina Boutique – Yönetim girişi" başlıklı, e-posta ve şifre alanı olan bir form.
3. **Giriş bilgileri** (seed’de oluşturduğumuz):
   - E-posta: **admin@luminaboutique.com**
   - Şifre: **Admin123!**
4. Bu bilgileri yazıp **Giriş yap** deyin.
5. **Görmeniz gereken:** Kontrol paneli (dashboard): bugünkü sipariş, toplam sipariş, ürün sayısı, üye sayısı kartları; sipariş varsa **Son siparişler** tablosu (son 10 sipariş, Detay linki, "Tüm siparişler" linki).
6. **Bu ne demek?** Form POST ile gönderiliyor; `AuthController` veritabanından kullanıcıyı bulup şifreyi kontrol ediyor; doğruysa oturum açılıyor ve `/admin` sayfasına yönlendiriliyorsunuz.

---

### 3. Admin panelinden çıkışı test etmek

1. Zaten giriş yaptıysanız dashboard’da **Çıkış** linkine tıklayın.
2. **Görmeniz gereken:** Tekrar giriş sayfasına (`/admin/login`) dönülmesi.
3. Artık `/admin` adresine gitmeyi denerseniz, oturum olmadığı için tekrar giriş sayfasına yönlendirilirsiniz.

---

### 4. Admin sol menüyü test etmek

1. Admin giriş yaptıktan sonra **http://localhost:8888/admin** sayfasındasınız.
2. **Görmeniz gereken:** Solda koyu gri bir menü (Lumina Boutique, Kontrol paneli, Kategoriler, Ürünler, Siparişler, Çıkış). Sağ üstte "Mağazayı aç" linki.
3. **Kategoriler**e tıklayın → adres `/admin/categories` olmalı, sayfada "Kategoriler" başlığı ve "Kategori listesi ve ekleme/düzenleme bu sayfada olacak" metni görünür.
4. **Ürünler**e tıklayın → `/admin/products`, benzer placeholder metni.
5. **Siparişler**e tıklayın → `/admin/orders`, benzer placeholder metni.
6. **Kontrol paneli**ye tıklayın → tekrar dashboard (özet kartlar).
7. **Bu ne demek?** Tüm admin sayfaları artık ortak bir layout (sol menü + üst bar) kullanıyor.

---

### 5. Kategori yönetimini ve silmeyi test etmek

1. Admin giriş yapın, sol menüden **Kategoriler**e gidin.
2. **Liste:** Henüz kategori yoksa "Henüz kategori yok" yazar. "Yeni kategori" butonu sağ üstte görünür.
3. **Yeni kategori:** "Yeni kategori"ye tıklayın → `/admin/categories/create`. Formda: Kategori adı (zorunlu), Üst kategori (dropdown), Açıklama, Sıra, Aktif (checkbox). Örnek: Ad **Giyim**, Üst kategori **Yok**, Sıra **0**, Aktif işaretli → **Kaydet**.
4. **Kayıt sonrası:** Liste sayfasına döner; tabloda "Giyim" satırı görünür (Üst kategori: —, Sıra: 0, Durum: Aktif).
5. **İkinci kategori (alt):** Yine "Yeni kategori" → Ad **Elbise**, Üst kategori **Giyim**, Kaydet. Listede "Elbise" satırında Üst kategori "Giyim" görünür.
6. **Düzenleme:** Bir satırdaki "Düzenle" linkine tıklayın → `/admin/categories/edit?id=1`. Form mevcut değerlerle dolu. Adı veya sırayı değiştirip **Güncelle** deyin; liste güncellenir.
7. **Hata kontrolü:** Yeni kategoride adı boş bırakıp Kaydet derseniz "Kategori adı zorunludur" hatası görünür; form doldurulan alanlarla tekrar açılır.
8. **Silme:** Listede bir satırda **Sil** linkine tıklayın → onay sayfası açılır ("Bu kategoriyi silmek istediğinize emin misiniz?"). **Evet, sil** derseniz kategori silinir; alt kategoriler üst kategorisiz, bu kategorideki ürünler kategorisiz kalır. **İptal** ile listeye dönersiniz.

---

### 6. Ürün yönetimini test etmek

1. Sol menüden **Ürünler**e gidin. Henüz ürün yoksa "Henüz ürün yok" yazar; "Yeni ürün" butonu sağ üstte görünür.
2. **Yeni ürün:** "Yeni ürün"e tıklayın → `/admin/products/create`. Form: Ürün adı (zorunlu), Kategori, Açıklama, Fiyat, Stok, **Ürün görseli** (isteğe bağlı; JPG/PNG/WebP, max 2 MB), Öne çıkan / Yeni / Aktif (checkbox). Görsel seçip **Kaydet** derseniz ürün kaydıyla birlikte görsel `uploads/products/` altına yüklenir ve mağazada gösterilir.
3. **Kayıt sonrası:** Ürün listesine döner; tabloda ürün adı, kategori, SKU, fiyat, stok, durum ve **Düzenle** linki görünür. İndirimli fiyat varsa hem indirimli hem eski fiyat gösterilir.
4. **Düzenleme:** Bir satırdaki **Düzenle** linkine tıklayın → form mevcut değerlerle dolu. Fiyat veya stok değiştirip **Güncelle** deyin; liste güncellenir.
5. **Hata kontrolü:** Yeni üründe adı boş bırakıp veya fiyatı negatif girip Kaydet derseniz hata mesajı görünür; form doldurulan alanlarla tekrar açılır.
6. **Ürün silme:** Listede **Sil** → onay sayfası. Siparişte kullanılan ürün silinemez (hata mesajı gösterilir).

---

### 7. Mağaza: kategoriler ve ürünleri test etmek

1. **Anasayfa:** http://localhost:8888/ adresini açın. Panelde kategori ve ürün eklediyseniz **Kategoriler** listesi ve **Öne çıkan ürünler** (veya son ürünler) görünür.
2. **Kategori sayfası:** Anasayfadaki bir kategori linkine (örn. Giyim) tıklayın → adres `/kategori/giyim` (slug’a göre değişir). O kategorideki aktif ürünler listelenir; her ürüne tıklanınca ürün detay sayfasına gider.
3. **Ürün detay:** Bir ürün linkine tıklayın → adres `/urun/urun-slug`. Ürün adı, kategori, fiyat (indirimli varsa eski fiyat üstü çizili), açıklama, stok bilgisi görünür. Ürün görseli ve “Sepete ekle” sonraki adımda eklenecek.
4. **Kırık link:** Var olmayan bir slug ile `/kategori/xyz` veya `/urun/xyz` açarsanız 404 (Kategori/Ürün bulunamadı) görünür.

---

### 7b. Ürün detay: beden seçimi ve sepete ekleme (test listesi)

1. **Varyantı olmayan ürün (basit ürün):** Ürün detayda beden seçmeden **Sepete Ekle** butonu devre dışı (soluk) olmalı; "Lütfen beden seçin." uyarısı görünür. Bir beden seçin (örn. M) → buton aktif olur. Sepete ekleyin → sepette **Beden: M** görünür. Aynı ürünü farklı bedenle (L) ekleyin → sepette iki ayrı satır olmalı. Sunucu tarafında beden gönderilmeden istek "Lütfen beden seçin." hatası ile reddedilir.
2. **Varyantlı ürün:** Sayfada dropdown'lar (— Seçin —) görünür; hepsi seçilmeden buton devre dışı, "Lütfen varyant seçin." yazar. Varyantları seçip sepete ekleyin → sepette varyant bilgisiyle listelenir.
3. **Stok dışı beden:** Stokta olmayan beden (örn. XL) gri ve tıklanamaz görünür.

---

### 8. Sepet ve ödemeyi test etmek

1. **Sepete ekle:** Ürün detayda **beden seçip** (veya varyantlı ürünse varyant seçip) **Sepete ekle** deyin → sepete yönlendirilirsiniz. Header’da **Sepet (1)** veya **Sepet (2)** gibi adet görünür.
2. **Sepet sayfası:** `/sepet` — Ürün listesi, fiyat, adet (güncelle butonu), toplam, **Kaldır** linki. Adeti değiştirip **Güncelle** veya **Kaldır** ile ürünü çıkarın. **Ödemeye geç** ile ödeme sayfasına gider.
3. **Ödeme formu:** `/odeme` — E-posta, ad, soyad, telefon, il, ilçe, adres (zorunlu); posta kodu, sipariş notu (isteğe bağlı). Ödeme yöntemi: **Kapıda ödeme**, **Havale/EFT**, **Kredi kartı (Stripe — yakında)**. **Siparişi tamamla** deyin.
4. **Sipariş sonrası:** Eksik alan varsa hata mesajları görünür; form doldurulmuş alanlarla tekrar açılır. Tümü dolu ve geçerliyse sipariş oluşturulur, sepet boşalır, **Siparişiniz alındı** sayfasına yönlendirilirsiniz; sipariş numarası (örn. LB-20250204-1234) gösterilir.
5. **Panelde sipariş:** Admin → Siparişler sayfasında sipariş listesi görünür. Bir siparişin **Detay** linkine tıklayın → sipariş detay sayfasında müşteri bilgisi, adres, ürünler, toplam ve **Sipariş durumunu güncelle** formu görünür. Durumu değiştirip **Durumu güncelle** deyin; durum geçmişi sayfada listelenir. Stok otomatik düşülmüş olur.

---

### 9. Admin sipariş detay ve durum güncellemeyi test etmek

1. Admin giriş yapın, sol menüden **Siparişler**e gidin.
2. Listede bir sipariş satırında **Detay** linkine tıklayın → adres `/admin/orders/show?id=X` olur.
3. **Görmeniz gereken:** Sipariş numarası, müşteri bilgisi (ad, e-posta, telefon), teslimat adresi, ödeme yöntemi, sipariş kalemleri tablosu (ürün, SKU, adet, birim fiyat, toplam), ara toplam / kargo / toplam, varsa müşteri notu.
4. **Durum güncelleme:** "Sipariş durumunu güncelle" bölümünde durum açılır listesinden yeni durum seçin (örn. Onaylandı, Hazırlanıyor, Kargoda). İsteğe bağlı not yazıp **Durumu güncelle** deyin.
5. **Görmeniz gereken:** Sayfa yenilenir, "Sipariş durumu güncellendi." mesajı görünür; "Durum geçmişi" listesinde yeni kayıt eklenmiş olur.
6. **Kargo bilgisi ekle:** Sipariş detay sayfasında "Kargo bilgisi ekle" bölümünde kargo firması (örn. Yurtiçi, Aras) ve takip numarası girin. İsterseniz "Sipariş durumunu Kargoda yap" kutusu işaretliyse kargo eklenirken sipariş durumu otomatik **Kargoda** olur. **Kargo ekle** deyin → "Kargo bilgisi eklendi." mesajı görünür; eklenen kargo "Kargo bilgisi" tablosunda listelenir.
7. **Sipariş listesine dön:** Sayfanın altındaki veya üstündeki "← Sipariş listesine dön" linki ile listeye dönebilirsiniz.

---

### 10. Mağaza: footer, İletişim ve Hakkımızda sayfalarını test etmek

1. Mağaza anasayfasını veya herhangi bir mağaza sayfasını açın. Sayfanın altında **footer** görünür: **İletişim**, **Hakkımızda** linkleri ve "© 2025 Lumina Boutique. Tüm hakları saklıdır." metni.
2. **İletişim:** Footer’daki **İletişim** linkine tıklayın veya `/iletisim` adresine gidin. İletişim bilgileri (e-posta, telefon, adres) ve **Bize yazın** formu görünür. Formu doldurup **Gönder** deyin; mesaj `contact_messages` tablosuna kaydedilir ve "Mesajınız alındı." mesajı görünür.
3. **Hakkımızda:** Footer’daki **Hakkımızda** linkine tıklayın veya `/hakkimizda` adresine gidin. Hakkımızda metni ve İletişim linki görünür.

---

### 11. Ürün görseli yükleme ve mağazada göstermeyi test etmek

1. **Admin:** Ürünler → Yeni ürün veya bir ürünü Düzenle. **Ürün görseli** alanından JPG, PNG veya WebP dosyası seçin (max 2 MB). Kaydet veya Güncelle deyin.
2. **Mağaza anasayfa:** Öne çıkan ürünlerde yüklediğiniz görsel varsa kartın üstünde görünür; yoksa "Görsel yok" yazar.
3. **Kategori sayfası:** Ürün kartlarında aynı şekilde görsel veya "Görsel yok" görünür.
4. **Ürün detay:** Sayfanın solunda ürün görseli (yüklendiyse) veya "Ürün görseli yok" metni görünür. Düzenleme sayfasında **Yeni görsel ekle** ile ek görsel ekleyebilirsiniz (birden fazla görsel desteklenir; mağazada ilk görsel kullanılır).

---

### 12. Admin: iletişim mesajlarını test etmek

1. Admin giriş yapın. Sol menüde **İletişim mesajları** linki görünür.
2. **İletişim mesajları**na tıklayın → adres `/admin/contact-messages`. Mağaza /iletisim formundan gönderilen mesajlar listelenir (Gönderen, E-posta, Konu, Tarih, Durum: Yeni/Okundu).
3. **Görüntüle** linkine tıklayın → mesaj detay sayfası açılır; mesaj otomatik olarak **Okundu** işaretlenir. Listeye dönünce o satırda "Okundu" görünür.

---

### 13. Sabit sayfalar (Grup 5)

1. **Seed (ilk kurulum):** Proje kökünden `php database/seeds/seed_pages.php` çalıştırın. SSS, İade koşulları, KVKK, Mesafeli satış sözleşmesi ve Hakkımızda için `pages` tablosuna örnek kayıtlar eklenir.
2. **Admin:** Sol menüden **Sayfalar**a gidin → `/admin/pages`. Sayfa listesi (slug, başlık, sıra, durum); **Yeni sayfa**, **Düzenle**, **Görüntüle**, **Sil** linkleri.
3. **Yeni sayfa:** "Yeni sayfa" → slug (örn. `sss`), başlık, içerik (metin alanı), meta başlık/açıklama, sıra, Aktif (checkbox). Kaydet → liste güncellenir.
4. **Mağaza:** Footer’da İletişim, Hakkımızda, SSS, İade koşulları, Gizlilik/KVKK, Mesafeli satış sözleşmesi linkleri. Her biri `/sayfa/slug` adresine gider (örn. `/sayfa/sss`, `/sayfa/iade-kosullari`, `/sayfa/kvkk`, `/sayfa/mesafeli-satis-sozlesmesi`, `/sayfa/hakkimizda`).
5. **Sayfa içeriği:** Slug ile açılan sayfada başlık ve içerik (panelden düzenlenen metin) gösterilir. Kayıt yoksa veya pasifse 404.

---

### 14. Veritabanında ne var?

1. **phpMyAdmin** açın (veya benzeri): http://localhost:8888/phpMyAdmin/ (veya MAMP’teki MySQL portuna göre).
2. Sol taraftan **lumina_db** veritabanını seçin.
3. **Tablolar:** users, admin_users, admin_roles, categories, products, orders vb. hepsi burada. Şu an çoğu **boş**; sadece `admin_roles` ve `admin_users` içinde seed ile eklediğimiz bir rol ve bir admin kullanıcı var.
4. İleride kategori ve ürün ekledikçe `categories` ve `products` tablolarında satırlar göreceksiniz.

---

## Sırada ne gelecek? (İşlevler sırası)

Her adımda **ne eklendiğini** ve **nasıl test edeceğinizi** kısaca yazacağım.

| Sıra | Eklenecek özellik | Nerede test edersiniz? | Göreceğiniz şey |
|------|-------------------|------------------------|-----------------|
| ~~1~~ | ~~Admin sol menü~~ | ~~/admin~~ | **Tamamlandı.** Sol menü: Kontrol paneli, Kategoriler, Ürünler, Siparişler, Çıkış. |
| ~~2~~ | ~~Kategori yönetimi~~ | ~~/admin/categories~~ | **Tamamlandı.** Liste, yeni kategori, düzenleme. “Yeni kategori” butonu, ekleme/düzenleme formu |
| ~~3~~ | ~~Ürün yönetimi~~ | `/admin` → Ürünler | Ürün listesi, “Yeni ürün” butonu, ekleme/düzenleme (kategori, fiyat, stok) |
| ~~4~~ | ~~Mağaza: kategoriler~~ | `/` veya `/kategoriler` | Anasayfada veya ayrı sayfada kategori listesi (admin’de eklediğiniz kategoriler) |
| ~~5~~ | ~~Mağaza: ürün listesi~~ | ~~/kategori/slug~~ | **Tamamlandı.** Kategori sayfasında ürünler listelenir. |
| ~~6~~ | ~~Mağaza: ürün detay~~ | Ürün linki | **Tamamlandı.** Ürün sayfası: fiyat, açıklama, sepete ekle. |
| ~~7~~ | ~~Sipariş detay + durum + kargo~~ | /admin/orders/show | **Tamamlandı.** Sipariş detayı, durum güncelleme, kargo takip no. |
| ~~8~~ | ~~Footer + İletişim + Hakkımızda~~ | /iletisim, /hakkimizda | **Tamamlandı.** Footer (İletişim, Hakkımızda, telif). İletişim formu veritabanına kaydedilir. |
| ~~9~~ | ~~Admin: iletişim mesajları~~ | /admin/contact-messages | **Tamamlandı.** Liste (gönderen, e-posta, konu, tarih, okundu), detay (okundu işaretlenir). |
| ~~10~~ | ~~Ürün görselleri~~ | Admin ürün + mağaza | **Tamamlandı.** Yeni ürün/düzenlemede görsel yükleme (JPG/PNG/WebP, 2 MB). Anasayfa, kategori ve ürün detayda görsel gösterimi. |
| ~~11~~ | ~~Dashboard: son siparişler~~ | /admin | **Tamamlandı.** Kontrol panelinde son 10 sipariş listesi, Detay linki, "Tüm siparişler" linki. |
| ~~12~~ | ~~Sipariş yazdırma (Grup 1)~~ | /admin/orders/show → **Yazdır** | **Tamamlandı.** Sipariş detayda "Yazdır" butonu; yeni sekmede yazdırma dostu fiş sayfası açılır, tarayıcıdan yazdırabilirsiniz. |
| ~~13~~ | ~~Düşük stok uyarısı (Grup 1)~~ | /admin (Dashboard) | **Tamamlandı.** Stok eşiğinin altındaki ürünler listesi (en fazla 20); "Düzenle" ile ürün sayfasına gidilir. |
| ~~14~~ | ~~Ürün görseli silme (Grup 1)~~ | /admin/products/edit | **Tamamlandı.** Mevcut görsel(ler)in yanında "Kaldır" butonu; onay sonrası görsel veritabanı ve dosyadan silinir. |
| ~~15~~ | ~~Sipariş listesi arama/filtre (Grup 1)~~ | /admin/orders | **Tamamlandı.** Arama (sipariş no, müşteri, e-posta), durum filtresi, tarih aralığı (başlangıç/bitiş), "Filtrele" ve "Temizle". |
| ~~16~~ | ~~Ürün listesi arama/filtre (Grup 1)~~ | /admin/products | **Tamamlandı.** Arama (ürün adı, SKU), kategori filtresi, stok durumu (Stokta / Düşük stok / Stok yok), "Filtrele" ve "Temizle". |
| ~~17~~ | ~~Üye kayıt (Grup 2)~~ | /kayit | **Tamamlandı.** E-posta, şifre, ad, soyad, telefon ile kayıt; kayıt sonrası Hesabım’a yönlendirme. |
| ~~18~~ | ~~Müşteri giriş/çıkış (Grup 2)~~ | /giris, /cikis | **Tamamlandı.** Header’da Giriş/Kayıt; giriş sonrası Hesabım, Çıkış. redirect parametresi ile yönlendirme. |
| ~~19~~ | ~~Hesabım + Siparişlerim + Adreslerim + Bilgilerim (Grup 2)~~ | /hesabim | **Tamamlandı.** Siparişlerim (liste, detay, kargo takip), Adreslerim (ekle/düzenle/sil), Bilgilerim (ad, telefon, şifre değiştir). |
| ~~20~~ | ~~Üye ile sipariş + Profil (Grup 2)~~ | /odeme, /hesabim/bilgilerim | **Tamamlandı.** Ödemede giriş yapmışsa “Kayıtlı adresim” seçimi; siparişe user_id yazılır. Profil: ad, telefon, şifre. |
| ~~21~~ | ~~Admin Müşteriler (Grup 2)~~ | /admin/customers | **Tamamlandı.** Müşteri listesi (ad, e-posta, telefon ile arama), detay (iletişim, kayıtlı adresler, sipariş geçmişi). |
| ~~22~~ | ~~Kupon CRUD (Grup 3)~~ | /admin/coupons | **Tamamlandı.** Kupon listesi (kod, tip, değer, min. sepet, kullanım, geçerlilik), yeni kupon, düzenleme, silme. |
| ~~23~~ | ~~Sepette/Ödemede kupon (Grup 3)~~ | /odeme | **Tamamlandı.** Ödeme formunda kupon kodu alanı; geçerli kupon uygulanınca indirim ve toplam güncellenir; siparişe coupon_id ve discount_amount yazılır. |
| ~~24~~ | ~~Satış raporu (Grup 3)~~ | /admin/reports/sales | **Tamamlandı.** Tarih aralığına göre sipariş sayısı, toplam satış tutarı, en çok satan 20 ürün. |
| ~~25~~ | ~~Stok raporu (Grup 3)~~ | /admin/reports/stock | **Tamamlandı.** Tüm ürünler stok sırasına göre; düşük stok uyarısı listesi ve Düzenle linki. |
| ~~26~~ | ~~Ürün arama / Sıralama / Sayfalama / Etiketler (Grup 4)~~ | /arama, /kategori/slug | **Tamamlandı.** Arama (header formu), sıralama (fiyat, yenilik, isim), sayfalama, öne çıkan/yeni/indirim etiketleri. |
| ~~27~~ | ~~Sabit sayfalar (Grup 5)~~ | /admin/pages, /sayfa/slug | **Tamamlandı.** Admin: Sayfalar CRUD (liste, yeni, düzenle, sil). Mağaza: /sayfa/:slug ile sayfa gösterimi; footer’da Hakkımızda, SSS, İade, KVKK, Mesafeli satış linkleri. Seed: `database/seeds/seed_pages.php`. |
| ~~28~~ | ~~Benzer ürünler (Grup 6)~~ | /urun/slug | **Tamamlandı.** Ürün detay sayfasında "Bunları da beğenebilirsiniz" – aynı kategoriden veya rastgele 4 ürün. |
| ~~29~~ | ~~Dashboard grafik (Grup 6)~~ | /admin | **Tamamlandı.** Son 30 gün günlük satış grafiği (bar), iptal/iade hariç. |
| ~~30~~ | ~~Anasayfa slider (Grup 6)~~ | /, /admin/sliders | **Tamamlandı.** Admin: Slider CRUD (görsel, başlık, alt başlık, link). Anasayfada slider; önceki/sonraki ve 5 sn otomatik geçiş. |
| ~~31~~ | ~~Favori listesi (Grup 6)~~ | /hesabim/favoriler, ürün sayfası | **Tamamlandı.** Giriş yapmış üye: ürün sayfasında "Favorilere ekle" / "Favorilerden çıkar"; Hesabım → Favorilerim; header’da Favorilerim linki. |
| — | **Grup 6 kalan (isteğe bağlı)** | — | Sipariş onay e-postası (A25), beden/renk varyant (ayrı plan). → **docs/PROJE_EL_KITABI_VE_KALAN_GRUPLAR.md** |
| — | **Tasarım / Detaylandırma sırası** | — | Tasarım adımı (logo, responsive) ile detaylandırmalar (beden/renk, varyant) ne zaman? → **docs/TASARIM_VE_DETAYLANDIRMA_PLANI.md** |

Her özellik bittiğinde size “Şu adresi açın, şunu yapın, şunu görmelisiniz” diye kısa bir özet vereceğim; bu rehber de güncellenecek.

---

## Sorun çıkarsa

- **404 / Sayfa bulunamadı:** Document root’un `public` olduğunu ve `.htaccess` kurallarının çalıştığını kontrol edin (bkz. **docs/MAMP_APACHE_AYARLARI.md**).
- **Veritabanı hatası:** `.env` içindeki `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` değerlerini kontrol edin; MAMP’te MySQL’in çalıştığından emin olun.
- **Giriş yapamıyorum:** Seed’i çalıştırdığınızdan emin olun (bkz. **docs/SCHEMA_NASIL_CALISTIRILIR.md** – MAMP PHP yolu ile).

Bu rehber, geliştirme boyunca “şu an ne yaptık, nerede test ediyoruz?” sorusunun cevabı olacak şekilde güncellenecek.

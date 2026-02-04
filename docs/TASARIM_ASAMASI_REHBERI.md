# Lumina Boutique – Tasarım Aşaması Rehberi

**Amaç:** Testler tamamlandı; bundan sonraki aşamada **yalnızca tasarıma** odaklanılacak. Bu dosya, tasarım çalışması yaparken hangi sayfaların / bölümlerin nerede olduğunu ve hangi sırayla ele alınabileceğini listeler. **Kodda işlev değişikliği yapılmaz;** yalnızca görsel düzen (CSS, renk, tipografi, logo, responsive) güncellenir.

---

## 1. Tasarım kapsamı

| Bölüm | Açıklama |
|-------|----------|
| **Görsel kimlik** | Logo, favicon, renk paleti, tipografi (font ailesi, başlık/gövde boyutları) |
| **Mağaza arayüzü** | Header, footer, anasayfa, kategori/ürün listesi, ürün detay, sepet, ödeme, hesabım, iletişim, sabit sayfalar |
| **Admin arayüzü** | Giriş sayfası, sol menü, üst bar, dashboard, tablolar, formlar, butonlar |
| **Responsive** | Mobil ve tablette düzgün görünüm; menü, grid, font boyutları |

---

## 2. Mağaza – Hangi sayfa nerede?

Tüm mağaza sayfaları **ortak layout** kullanır: `app/Views/frontend/layouts/main.php`. Bu dosyada `<head>` içinde kısa bir `<style>` bloğu ve header/footer HTML’i var. İçerik `<?= $content ?? '' ?>` ile ortaya gelir.

| Sayfa / bölüm | İçeriğin geldiği view dosyası |
|---------------|------------------------------|
| **Layout (header, footer, genel stil)** | `app/Views/frontend/layouts/main.php` |
| Anasayfa (slider, kategoriler, öne çıkan ürünler) | `app/Views/frontend/home.php` |
| Kategori sayfası (ürün listesi) | `app/Views/frontend/category/show.php` |
| Arama sonuçları | `app/Views/frontend/search/index.php` |
| Ürün detay | `app/Views/frontend/product/show.php` |
| Sepet | `app/Views/frontend/cart/index.php` |
| Ödeme formu | `app/Views/frontend/checkout/form.php` |
| Sipariş tamamlandı | `app/Views/frontend/checkout/success.php` |
| Giriş / Kayıt | `app/Views/frontend/auth/login.php`, `register.php` |
| Hesabım (özet, siparişler, adresler, bilgilerim, favoriler) | `app/Views/frontend/account/index.php`, `orders.php`, `order_show.php`, `addresses.php`, `address_form.php`, `profile.php`, `favoriler.php` |
| İletişim | `app/Views/frontend/contact/index.php` |
| Sabit sayfa (Hakkımızda, SSS, İade, KVKK, Mesafeli satış) | `app/Views/frontend/pages/show.php` (ve isteğe bağlı `about.php`) |

**Stil stratejisi:** Şu an stiller büyük ölçüde **inline `style="..."`** ve layout’taki küçük `<style>` ile veriliyor. Tasarım aşamasında istenirse:
- Ortak stiller `public/assets/css/` altında bir veya birkaç CSS dosyasına taşınabilir.
- Layout’ta bu dosyalar `<link rel="stylesheet" href="...">` ile dahil edilir.
- Sayfa bazlı ek stiller yine ilgili view’larda veya ayrı CSS dosyalarında tutulabilir.

---

## 3. Admin – Hangi sayfa nerede?

Admin tarafında da **ortak layout** kullanılır: `app/Views/admin/layouts/main.php`. Sol menü, üst bar ve `<head>` içindeki stil burada.

| Sayfa / bölüm | İçeriğin geldiği view dosyası |
|---------------|------------------------------|
| **Layout (sidebar, üst bar, genel stil)** | `app/Views/admin/layouts/main.php` |
| Giriş (login) | `app/Views/admin/login.php` |
| Dashboard | `app/Views/admin/dashboard.php` |
| Kategoriler (liste, form) | `app/Views/admin/categories/index.php`, `create.php`, `edit.php`, `delete.php` |
| Özellikler / Beden–Renk | `app/Views/admin/attributes/index.php`, `form.php` |
| Ürünler (liste, yeni, düzenle, sil) | `app/Views/admin/products/index.php`, `create.php`, `edit.php`, `delete.php` |
| Siparişler (liste, detay, yazdır) | `app/Views/admin/orders/index.php`, `show.php`, `print.php` |
| Müşteriler | `app/Views/admin/customers/index.php`, `show.php` |
| Kuponlar | `app/Views/admin/coupons/index.php`, `form.php`, `delete.php` |
| Raporlar | `app/Views/admin/reports/index.php`, `sales.php`, `stock.php` |
| İletişim mesajları | `app/Views/admin/contact_messages/index.php`, `show.php` |
| Sayfalar | `app/Views/admin/pages/index.php`, `form.php` |
| Slider | `app/Views/admin/sliders/index.php`, `form.php` |
| Ayarlar | `app/Views/admin/settings/index.php` |

---

## 4. Önerilen tasarım sırası

1. **Global (tek seferde)**  
   Renk paleti, font ailesi, varsayılan başlık/gövde boyutları, link rengi. Logo ve favicon (varsa) yerleştirilecek alanların belirlenmesi.  
   **Dosyalar:** `frontend/layouts/main.php`, `admin/layouts/main.php`; istenirse `public/assets/css/global.css` (veya benzeri).

2. **Mağaza: Header ve footer**  
   Üst menü, logo alanı, arama kutusu, sepet linki; alt bilgi, link grupları, telif.  
   **Dosya:** `frontend/layouts/main.php`.

3. **Mağaza: Anasayfa**  
   Slider, kategori blokları, öne çıkan ürün kartları, boşluklar ve hiyerarşi.  
   **Dosya:** `frontend/home.php`.

4. **Mağaza: Liste sayfaları**  
   Kategori ve arama sonuçları: ürün kartları, grid, etiketler (Öne çıkan, Yeni, İndirim).  
   **Dosyalar:** `frontend/category/show.php`, `frontend/search/index.php`.

5. **Mağaza: Ürün detay**  
   Görsel alanı, başlık, fiyat, beden/renk seçimi, sepete ekle, benzer ürünler.  
   **Dosya:** `frontend/product/show.php`.

6. **Mağaza: Sepet ve ödeme**  
   Tablo / kart düzeni, butonlar, form alanları, özet kutusu.  
   **Dosyalar:** `frontend/cart/index.php`, `frontend/checkout/form.php`, `frontend/checkout/success.php`.

7. **Mağaza: Hesabım, giriş, kayıt, iletişim, sabit sayfalar**  
   Formlar, listeler, metin alanları; layout ile uyumlu boşluk ve tipografi.  
   **Dosyalar:** `frontend/account/*.php`, `frontend/auth/*.php`, `frontend/contact/index.php`, `frontend/pages/show.php`.

8. **Admin: Layout ve giriş**  
   Sidebar, üst bar, renkler; login sayfası.  
   **Dosyalar:** `admin/layouts/main.php`, `admin/login.php`.

9. **Admin: İç sayfalar**  
   Dashboard kartları ve grafik; tablolar; formlar (kategori, ürün, sipariş, kupon vb.).  
   **Dosyalar:** `admin/dashboard.php`, `admin/categories/*.php`, `admin/products/*.php`, `admin/orders/*.php` vb.

10. **Responsive**  
    Tüm bu sayfalarda mobil/tablet kırılımları; menü (hamburger vb.), grid (tek/çok sütun), font ve padding ölçeklemesi.  
    **Dosyalar:** Yukarıdaki ilgili view’lar + ortak CSS (veya layout içi medya sorguları).

---

## 5. Neler iyileştirilebilir? (fikir listesi)

- **Renk:** Arka plan, metin, vurgu ve buton renkleri; tutarlı bir palet.
- **Tipografi:** Başlık ve gövde fontu; boyut ve satır yüksekliği.
- **Logo ve favicon:** Header’da logo alanı; sekme ikonu.
- **Boşluklar:** Sayfa kenar boşlukları, bölüm araları, kart içi padding.
- **Butonlar:** Birincil / ikincil ayrımı; hover ve focus durumları.
- **Kartlar:** Ürün kartı, özet kutusu, dashboard kartı gölge ve köşe.
- **Formlar:** Input, select, textarea; label ve hata mesajı stili.
- **Tablolar:** Admin listelerde çizgi, zebra, başlık stili.
- **Responsive:** Breakpoint’ler (örn. 768px, 1024px); menü, grid, font küçülmesi.

---

## 6. Asset klasörleri (hazır)

Projede stil ve görsel için kullanılabilecek klasörler:

- `public/assets/css/` — CSS dosyaları
- `public/assets/images/` — Genel görseller (logo vb.)
- `public/assets/fonts/` — Web fontları (isteğe bağlı)
- `public/assets/js/` — İsteğe bağlı ek script’ler (ör. menü, slider)
- `public/uploads/` — Yüklenen ürün/slider görselleri (içerik; tasarım değil)

Layout’ta `$baseUrl` kullanıldığı için örnek:  
`<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">`  
veya  
`<img src="<?= $baseUrl ?>/assets/images/logo.png" alt="Lumina Boutique">`  
gibi yollar ileride eklenebilir.

---

## 7. Özet

- **Odak:** Sadece tasarım; işlev değişikliği yok.
- **Mağaza:** Önce `frontend/layouts/main.php` (header/footer + global), sonra home, kategori, ürün, sepet, ödeme, hesabım ve diğer sayfalar.
- **Admin:** Önce `admin/layouts/main.php` ve `admin/login.php`, sonra dashboard ve diğer iç sayfalar.
- **En son:** Responsive düzenlemeleri tüm sayfalarda gözden geçirmek.

Bu rehber, tasarım aşamasında hangi dosyada neyin düzenleneceğini göstermek içindir; kodda işlev değişikliği yapılmaz.

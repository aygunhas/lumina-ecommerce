# Lumina Boutique – Eklenmesi Gereken İşlevler (Full E-Ticaret)

Bu dosya, **şu an olmayan** ve **tam kapsamlı bir e-ticaret sitesi** için eklenmesi gereken işlevleri öncelik sırasına göre listeler. Tasarım ve detaylandırma (beden/renk varyantları) öncesi **işlev odaklı** sıra takip edilir.

---

## Şu an ne var? (Özet)

| Bölüm | Mevcut |
|-------|--------|
| **Mağaza** | Anasayfa (kategoriler, öne çıkan ürünler), kategori sayfası, ürün detay (görsel, sepete ekle), sepet, ödeme formu, sipariş tamamlandı, footer, İletişim, Hakkımızda |
| **Admin** | Giriş, dashboard (özet kartlar + son siparişler), kategoriler CRUD, ürünler CRUD + görsel yükleme, siparişler (liste, detay, durum, kargo takip), iletişim mesajları |

Yani: Misafir alışveriş, sepet, ödeme, sipariş akışı ve panelde temel yönetim **çalışıyor**. Eksik olanlar aşağıda gruplar halinde listelenmiştir.

---

## Grup 1 – Faz 1 tamamlama (önce bunlar)

Tam kapsamlı e-ticaret için önce eklenmesi mantıklı olanlar.

| Sıra | İşlev | Açıklama | Nerede |
|------|--------|----------|--------|
| 1 | **Sipariş yazdırma (B24)** | Sipariş detay sayfasında "Yazdır" / sipariş fişi (yazdırma dostu sayfa veya PDF) | Admin → Sipariş detay |
| 2 | **Site ayarları (B35)** | Site adı, iletişim e-posta, telefon, adres (panelden düzenlenebilir; mağaza footer/iletişim buradan okuyabilir) | Admin → Ayarlar |
| 3 | **Kargo ayarları (B36)** | Sabit kargo ücreti, ücretsiz kargo eşiği (şu an sabit 0; ayarlardan okunacak) | Admin → Ayarlar |
| 4 | **Ödeme ayarları (B37)** | Kapıda/havale açık/kapalı, havale IBAN vb. (panelden; mağaza ödeme sayfası buna göre) | Admin → Ayarlar |
| 5 | **Düşük stok uyarısı (B8)** | Dashboard’da stok eşiğinin altındaki ürünler listesi | Admin → Dashboard |
| 6 | **Ürün görseli silme (B14)** | Ürün düzenlemede mevcut görseli kaldırma (product_images’dan sil) | Admin → Ürün düzenle |
| 7 | **Sipariş listesi arama/filtre (B20)** | Tarih, sipariş no, müşteri adı veya e-posta ile arama; duruma göre filtre | Admin → Siparişler |
| 8 | **Ürün listesi arama/filtre (B9)** | Ürün adı/SKU ile arama; kategori veya stok durumuna göre filtre | Admin → Ürünler |

---

## Grup 2 – Üye ve hesap (Faz 2 çekirdek)

Full e-ticaret için üye sistemi ve hesabım akışı.

| Sıra | İşlev | Açıklama | Nerede |
|------|--------|----------|--------|
| 9 | **Üye kayıt (A27)** | E-posta, şifre, ad soyad, telefon ile kayıt; `users` tablosuna kayıt | Mağaza → Kayıt sayfası |
| 10 | **Müşteri giriş/çıkış (A28)** | E-posta + şifre ile giriş; şifremi unuttum (isteğe bağlı) | Mağaza → Giriş / header |
| 11 | **Hesabım sayfası (A29)** | Siparişlerim, Adreslerim, Bilgilerim linkleri; tek sayfa veya alt sayfalar | Mağaza → Hesabım |
| 12 | **Siparişlerim (A30)** | Giriş yapmış kullanıcının sipariş listesi; detay, kargo takip no | Mağaza → Hesabım / Siparişlerim |
| 13 | **Adreslerim (A31)** | Kayıtlı teslimat adresleri ekleme/düzenleme/silme | Mağaza → Hesabım / Adreslerim |
| 14 | **Üye ile sipariş (A20)** | Ödeme sayfasında giriş yapmışsa kayıtlı adresleri seçme; `user_id` siparişe yazılır | Mağaza → Ödeme |
| 15 | **Profil güncelleme (A32)** | Ad, telefon, şifre değiştirme | Mağaza → Hesabım |
| 16 | **Müşteri listesi (B26)** | Üyeler listesi; iletişim bilgisi, son sipariş tarihi | Admin → Müşteriler |
| 17 | **Müşteri detay (B27)** | Tek müşteri: sipariş geçmişi, adresler, not | Admin → Müşteriler |
| 18 | **Müşteri arama (B28)** | Ad, e-posta, telefon ile arama | Admin → Müşteriler |

---

## Grup 3 – Kupon ve raporlar

| Sıra | İşlev | Açıklama | Nerede |
|------|--------|----------|--------|
| 19 | **Kupon oluşturma (B29)** | Kod, indirim tipi (yüzde/sabit), min. sepet, geçerlilik tarihi, kullanım limiti | Admin → Kuponlar |
| 20 | **Kupon listesi (B30)** | Kuponlar listesi; kullanım sayısı, kalan | Admin → Kuponlar |
| 21 | **Sepette kupon (A26)** | Ödeme/sepet sayfasında kupon kodu girme; indirim uygulama, toplam güncelleme | Mağaza → Sepet / Ödeme |
| 22 | **Satış raporu (B39)** | Tarih aralığına göre satış tutarı, sipariş sayısı; isteğe bağlı en çok satan ürünler | Admin → Raporlar |
| 23 | **Stok raporu (B40)** | Mevcut stok listesi; düşük stok uyarısı listesi | Admin → Raporlar / Stok |

---

## Grup 4 – Katalog ve bulunabilirlik

| Sıra | İşlev | Açıklama | Nerede |
|------|--------|----------|--------|
| 24 | **Ürün arama (A14)** | Kelime ile ürün arama; sonuç sayfası | Mağaza → Arama (header veya sayfa) |
| 25 | **Sıralama (A13)** | Kategori/arama sayfasında: fiyat (artan/azalan), yeniden eskiye, isme göre | Mağaza → Kategori sayfası |
| 26 | **Sayfalama (A9)** | Ürün listesinde sayfa başına 12/24 ürün; sayfa numaraları | Mağaza → Kategori, arama sonucu |
| 27 | **Öne çıkan / Yeni / İndirimli etiketleri (A15)** | Ürün kartında "Öne çıkan", "Yeni", "% indirim" etiketleri | Mağaza → Ürün kartları |

---

## Grup 5 – İçerik ve yasal

| Sıra | İşlev | Açıklama | Nerede |
|------|--------|----------|--------|
| 28 | **Sabit sayfalar panelden (B32)** | Hakkımızda, İletişim metni, SSS, İade, KVKK, Mesafeli satış – `pages` tablosundan panelden düzenleme; mağaza slug ile sayfa gösterir | Admin → Sayfalar; Mağaza → /sayfa/slug |
| 29 | **SSS sayfası (A36)** | Sıkça sorulan sorular (panelden veya sabit sayfa) | Mağaza → SSS |
| 30 | **İade & değişim (A37)** | Metin sayfası | Mağaza → İade koşulları |
| 31 | **Gizlilik & KVKK (A38)** | Gizlilik politikası sayfası | Mağaza → Gizlilik |
| 32 | **Mesafeli satış sözleşmesi (A39)** | Yasal metin sayfası | Mağaza → Mesafeli satış |

---

## Grup 6 – İsteğe bağlı / ileri

| Sıra | İşlev | Açıklama | Nerede |
|------|--------|----------|--------|
| 33 | **Sipariş onay e-postası (A25)** | Sipariş sonrası e-posta (SMTP/ayarlar gerekir) | Backend + B38 e-posta ayarları |
| 34 | **Dashboard grafik (B7)** | Aylık/haftalık satış grafiği | Admin → Dashboard |
| 35 | **Anasayfa slider (A1/B33)** | Slider resimleri ve metinleri (panelden) | Mağaza anasayfa + Admin |
| 36 | **Favori listesi (A33)** | Beğenilen ürünleri kaydetme (giriş gerekir) | Mağaza |
| 37 | **Benzer ürünler (A17)** | Ürün detayda "Bunları da beğenebilirsiniz" | Mağaza → Ürün detay |
| 38 | **Beden/renk ve varyant (detaylandırmalar)** | Panelde varyant yönetimi; mağazada beden/renk seçimi, varyant stok | Ayrı plan: DETAYLANDIRMALAR_SONRA.md |

---

## Önerilen uygulama sırası (full e-ticaret)

1. **Grup 1** (Faz 1 tamamlama): Sipariş yazdırma → Site/Kargo/Ödeme ayarları → Düşük stok uyarısı → Ürün görseli silme → Sipariş/Ürün listesi arama-filtre.  
2. **Grup 2** (Üye ve hesap): Üye kayıt → Giriş/çıkış → Hesabım (Siparişlerim, Adreslerim, Profil) → Üye ile sipariş → Admin müşteri listesi/detay/arama.  
3. **Grup 3** (Kupon ve raporlar): Kupon CRUD → Sepette kupon → Satış/Stok raporu.  
4. **Grup 4** (Katalog): Arama → Sıralama → Sayfalama → Etiketler.  
5. **Grup 5** (İçerik/yasal): Sabit sayfalar panelden → SSS, İade, KVKK, Mesafeli satış sayfaları.  
6. **Grup 6** (İsteğe bağlı): E-posta, grafik, slider, favori, benzer ürünler; ardından beden/renk varyantları (detaylandırmalar).

Bu sırayla ilerlerseniz önce **işlevsel olarak tam kapsamlı** bir e-ticaret sitesi oluşur; ardından tasarım ve detaylandırma adımlarına geçebilirsiniz.

---

## Kısa özet tablo

| Grup | İşlev sayısı | İçerik |
|------|----------------|--------|
| **1 – Faz 1 tamamlama** | 8 | Sipariş yazdırma, ayarlar (site/kargo/ödeme), düşük stok, görsel silme, sipariş/ürün arama-filtre |
| **2 – Üye ve hesap** | 10 | Kayıt, giriş, hesabım, siparişlerim, adreslerim, üye ile sipariş, profil, admin müşteri |
| **3 – Kupon ve raporlar** | 5 | Kupon CRUD, sepette kupon, satış/stok raporu |
| **4 – Katalog** | 4 | Arama, sıralama, sayfalama, etiketler |
| **5 – İçerik/yasal** | 5 | Sabit sayfalar panelden, SSS, İade, KVKK, Mesafeli satış |
| **6 – İsteğe bağlı** | 6+ | E-posta, grafik, slider, favori, benzer ürünler, varyant |

İsterseniz bir sonraki adımda **Grup 1**’den (ör. sipariş yazdırma veya site ayarları) başlayabiliriz.

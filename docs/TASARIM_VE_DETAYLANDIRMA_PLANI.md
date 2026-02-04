# Tasarım ve Detaylandırma – Ne Zaman?

Bu dosya, **tasarım adımı** (görsel düzen, responsive, logo vb.) ile **detaylandırmalar** (beden/renk, varyant stok vb.) arasındaki sırayı ve zamanlamayı netleştirir.

---

## Şu anki durum (Faz 1 işlevler)

| Bölüm | Durum |
|-------|--------|
| **Mağaza** | Anasayfa, kategoriler, ürün listesi/detay, görseller, sepet, ödeme, sipariş tamamlandı, footer, İletişim, Hakkımızda |
| **Admin** | Giriş, dashboard (özet + son siparişler), kategoriler, ürünler (görsel dahil), siparişler (detay, durum, kargo), iletişim mesajları |
| **Eksik (isteğe bağlı)** | Sipariş yazdırma, site/kargo ayarları, ürün görseli silme, düşük stok uyarısı vb. |

Yani **çekirdek e-ticaret akışı çalışıyor**; sırada ya ek işlevler, ya **tasarım**, ya da **detaylandırmalar** var.

---

## 1. Tasarım adımı – Ne demek?

- **Görsel kimlik:** Logo, favicon, renk paleti, tipografi (fontlar).
- **Mağaza arayüzü:** Header/footer düzeni, ürün kartları, butonlar, form stilleri; tutarlı ve “mağaza” hissi.
- **Admin arayüzü:** Tablolar, formlar, renkler; okunaklı ve sade.
- **Responsive (A5):** Mobil ve tablette düzgün görünüm; menü, grid, font boyutları.
- **İsteğe bağlı:** Slider (A1), öne çıkan etiketleri (A15), karanlık mod vb.

Tasarım adımı **işlev eklemeden** veya **az işlevle** yapılabilir; sayfaların “nasıl görüneceğini” sabitlemek için uygundur.

---

## 2. Detaylandırmalar – Ne demek?

(Bkz. **DETAYLANDIRMALAR_SONRA.md**)

- **Beden / renk:** `attributes`, `attribute_values` tabloları kullanılarak ürüne beden ve renk seçenekleri.
- **Varyant stok:** Her beden+renk için ayrı stok/fiyat (product_variants, product_variant_attribute_values).
- **Panel:** Varyant yönetimi (beden/renk ekleme, her varyant için SKU/stok/fiyat).
- **Mağaza:** Ürün sayfasında beden/renk seçimi, stoka göre “Sepete ekle” / “Stok yok”.
- **Sepet ve sipariş:** Sepette varyant bilgisi, siparişte varyant kaydı.

Detaylandırmalar **yeni işlev** ekler; veritabanı yapısı hazır, uygulama tarafı eklenecek.

---

## 3. Zamanlama seçenekleri

### Seçenek A – Önce tasarım, sonra detaylandırmalar

| Sıra | Adım | Açıklama |
|------|------|----------|
| 1 | **Tasarım** | Logo, renkler, responsive, mağaza/admin görünümünü toplu güncelle. |
| 2 | (İsteğe bağlı) | 1–2 ek işlev: sipariş yazdırma, basit ayarlar. |
| 3 | **Detaylandırmalar** | Beden/renk, varyant stok, panel + mağaza + sepet/sipariş. |

**Artı:** Site önce “bitmiş” görünür; yeni özellik eklerken tasarımı bozmamak kolaylaşır.  
**Eksi:** Varyant özelliği daha geç gelir.

---

### Seçenek B – Önce detaylandırmalar, sonra tasarım

| Sıra | Adım | Açıklama |
|------|------|----------|
| 1 | **Detaylandırmalar** | Beden/renk, varyant stok, panel + mağaza + sepet/sipariş. |
| 2 | **Tasarım** | Logo, responsive, görsel düzen. |

**Artı:** Ürün katalogu ve satış akışı önce zenginleşir.  
**Eksi:** Tasarımı sonra tüm sayfalara (varyantlı ürün sayfası dahil) uygulamak gerekir.

---

### Seçenek C – Birkaç işlev, sonra tasarım, sonra detay

| Sıra | Adım | Açıklama |
|------|------|----------|
| 1 | **1–2 ek işlev** | Sipariş yazdırma, basit site/kargo ayarları vb. (Faz 1’i tamamlama). |
| 2 | **Tasarım** | Logo, responsive, mağaza/admin görünümü. |
| 3 | **Detaylandırmalar** | Beden/renk, varyant stok. |

**Artı:** Faz 1 işlevsel olarak kapanır, ardından görsel ve sonra katalog detayı gelir.  
**Eksi:** Tasarım ve detaylandırma ikisi de daha geç başlar.

---

## 4. Öneri

- **Önceliğiniz “site profesyonel görünsün” ise:** **Seçenek A** (önce tasarım, sonra detaylandırmalar).
- **Önceliğiniz “beden/renk ve varyant stok hemen olsun” ise:** **Seçenek B** (önce detaylandırmalar, sonra tasarım).
- **“Önce Faz 1’i işlev olarak tam kapatalım” diyorsanız:** **Seçenek C** (birkaç işlev → tasarım → detaylandırmalar).

Hangi seçeneği benimseyeceğinizi belirledikten sonra, bir sonraki adımda doğrudan o sıraya göre ilerleyebiliriz (ör. “tasarım adımına geç” veya “detaylandırmalara geç” veya “şu 1–2 işlevi ekle”).

---

## 5. Özet tablo

| Ne zaman? | Tasarım adımı | Detaylandırmalar (beden/renk, varyant) |
|-----------|----------------|----------------------------------------|
| **Seçenek A** | Hemen sonra (sıradaki büyük adım) | Tasarım bittikten sonra |
| **Seçenek B** | Detaylandırmalar bittikten sonra | Hemen sonra (sıradaki büyük adım) |
| **Seçenek C** | 1–2 işlev sonrası | Tasarım bittikten sonra |

Bu dosyayı güncelleyerek seçiminizi (A, B veya C) ve isteğe bağlı notları yazabilirsiniz; buna göre sıradaki adımları netleştiririz.

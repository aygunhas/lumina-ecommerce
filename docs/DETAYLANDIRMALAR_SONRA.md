# Detaylandırmalar – Ne Zaman Yapılacak?

Bu dosya, **beden / renk**, **varyant bazlı stok** ve benzeri detayların ne zaman ele alınacağını açıklar.

---

## Öneri: Bu detaylara **sonra** bakalım

**Şu an:** Mağaza tarafı (kategoriler, ürün listesi, ürün detay, görseller, sepet, ödeme) ve panel (kategoriler, ürünler, siparişler, kargo, iletişim mesajları) çalışıyor. Her ürünün tek fiyatı ve tek stok değeri var.

**Önerilen sıra:**

1. **Önce (tamamlandı):** Mağaza tarafı — kategoriler, ürün listesi, ürün detay, görseller, sepet, ödeme.

2. **Sırada ne?** Tasarım adımı (logo, responsive, görsel düzen) ile detaylandırmalar (beden/renk, varyant) arasındaki sırayı **docs/TASARIM_VE_DETAYLANDIRMA_PLANI.md** dosyasında belirleyebilirsiniz. Orada A/B/C seçenekleri var.

3. **Sonra:** Ürün detaylandırmaları (beden/renk, varyant stok)  
   - Beden ve renk seçenekleri (attributes / attribute_values tabloları zaten var)  
   - Her beden+renk kombinasyonu için ayrı stok (product_variants + product_variant_attribute_values)  
   - Panelde: “Varyant yönetimi” (beden/renk ekleme, her varyant için SKU/stok/fiyat)  
   - Mağazada: Ürün sayfasında beden/renk seçimi ve stoka göre “Sepete ekle” / “Stok yok”

**Neden sonra?**

- Veritabanı yapısı **hazır** (attributes, attribute_values, product_variants, product_variant_attribute_values).  
- Ama varyantlı ürün akışı (panel + mağaza + sepet/sipariş) bir bütün: panelde varyant tanımlama, mağazada gösterme, sepette varyant bilgisi taşıma, siparişte varyant kaydetme.  
- Önce basit ürün listesi ve detay sayfasını bitirirsek, hem test etmek kolay olur hem de sonra sadece “varyant katmanını” ekleyerek ilerleriz.

---

## Veritabanında hazır olanlar

| Tablo | Açıklama |
|-------|----------|
| `attributes` | Beden, renk vb. özellik tipleri (type: size, color) |
| `attribute_values` | Değerler (S, M, L / Kırmızı, Mavi vb.); renk için `color_hex` alanı var |
| `product_variants` | Ürün başına varyant: sku, stock, price, sale_price |
| `product_variant_attribute_values` | Hangi varyantın hangi beden/renk değerine sahip olduğu (çoklu eşleştirme) |

Şu an ürünler **products** tablosundaki tek `stock` ve `price` ile yönetiliyor. Varyant kullanıldığında stok ve fiyat **product_variants** üzerinden takip edilecek; mağazada ve sepette “varyant” seçimi eklenecek.

---

## Sonuç

- **Ürün silme:** Eklendi (onay sayfası + siparişte kullanılan ürün silinmez, hata mesajı gösterilir).
- **Beden / renk ve varyant stok:** Veritabanı hazır; panel ve mağaza tarafındaki “varyant yönetimi” ve “varyantlı stok” özelliklerini **mağaza listesi ve ürün detayı çalıştıktan sonra** eklemek daha mantıklı.

Tasarım ve detaylandırma zamanlaması için: **docs/TASARIM_VE_DETAYLANDIRMA_PLANI.md**

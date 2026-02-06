# Ödeme Sayfası – Eksikler ve Eklenenler

Bu belge, ödeme (checkout) sayfasında olması gereken ancak eksik kalan öğeleri ve yapılan eklemeleri özetler.

---

## Eklenenler (Güncel Tasarımda)

| Öğe | Açıklama |
|-----|----------|
| **Kupon kodu** | Görünür kupon alanı (placeholder: Örn: HOSGELDIN10). Form gönderildiğinde kupon sunucuda doğrulanır. |
| **Uygulanan kupon** | Kupon geçerliyse yeşil kutu içinde kupon kodu, etiket ve indirim tutarı gösterilir. |
| **Ödeme yöntemi seçimi** | Kapıda ödeme, Havale/EFT, Kredi kartı (Yakında) radyo butonları. Ayarlara göre Kapıda ve Havale açılıp kapatılabilir. |
| **Havale/EFT bilgisi** | Havale seçildiğinde banka adı, hesap adı ve IBAN (admin ayarlarından) gösterilir. |
| **Kart bilgisi notu** | “Kart bilgileri Stripe entegrasyonu ile güvenli şekilde alınacaktır. Şu an Kapıda ödeme veya Havale/EFT kullanabilirsiniz.” metni. |
| **Sepete dön** | Sayfa altında “← Sepete dön” linki. |
| **Siparişi tamamla** | Buton metni “Siparişi tamamla” olarak güncellendi. |

---

## Eksik / İleride Eklenebilecekler

### 1. Kredi kartı bilgileri (kart numarası, son kullanma, CVV)

- **Durum:** Şu an alan yok; sadece “Kredi kartı (Yakında)” ve açıklama metni var.
- **Neden:** Kart verisi PCI-DSS kapsamında; güvenli olması için **Stripe (veya benzeri)** entegrasyonu ile alınmalı. Kart numarası sunucuya gönderilmemeli.
- **Yapılacak:** Stripe Elements (veya Checkout Session) ile kart alanları eklenip ödeme Stripe üzerinden tamamlanmalı.

### 2. Kupon “Uygula” butonu (sayfa yenilemeden)

- **Durum:** Kupon kodu formda var; form gönderilince (Siparişi tamamla) birlikte işleniyor. Ayrı bir “Uygula” ile anında doğrulama yok.
- **Yapılacak:** İsteğe bağlı: AJAX ile kupon doğrulama endpoint’i + “Uygula” butonu; uygulanınca indirim satırı anında güncellenir.

### 3. Mesafeli satış / sözleşme onayı

- **Durum:** Checkbox yok.
- **Yapılacak:** “Mesafeli satış sözleşmesini ve ön bilgilendirme formunu okudum, kabul ediyorum.” gibi checkbox; işaretlenmeden sipariş tamamlanmasın.

### 4. Gizlilik politikası onayı

- **Durum:** Checkbox yok.
- **Yapılacak:** “Kişisel verilerimin işlenmesine ilişkin aydınlatma metnini okudum, kabul ediyorum.” benzeri checkbox.

### 5. Fatura bilgisi (bireysel / kurumsal)

- **Durum:** Fatura tipi, vergi no, TC kimlik no vb. alanlar yok.
- **Yapılacak:** Bireysel/kurumsal seçimi, vergi/TC no alanları; sipariş ve fatura tablolarına uygun şekilde kayıt.

### 6. Kargo seçenekleri

- **Durum:** Tek kargo maliyeti/hesaplama var; alternatif kargo (express vb.) yok.
- **Yapılacak:** `shipping_methods` veya benzeri yapı ile birden fazla kargo seçeneği ve fiyatı.

### 7. Fatura adresi (teslimat adresinden farklı)

- **Durum:** Backend’de `billing_same_as_shipping = 1` sabit; fatura adresi ayrı seçilemiyor.
- **Yapılacak:** “Fatura adresi teslimat adresi ile aynı” checkbox + aynı değilse fatura alanları (ve veritabanına kayıt).

### 8. Güvenlik / güven rozetleri

- **Durum:** SSL / 3D Secure vb. görsel rozet yok.
- **Yapılacak:** “Güvenli ödeme”, “256-bit SSL” gibi kısa metin veya ikonlar (footer veya ödeme alanı yakınında).

### 9. Özet: Ödeme sayfasında olması gerekenler (kontrol listesi)

| Öğe | Durum |
|-----|--------|
| İletişim (e-posta) | Var |
| Teslimat adresi | Var |
| Kayıtlı adres seçimi | Var |
| Sipariş notu | Var |
| Kupon kodu | Var (eklendi) |
| Uygulanan kupon gösterimi | Var (eklendi) |
| Ödeme yöntemi seçimi | Var (eklendi) |
| Havale/EFT bilgileri | Var (eklendi) |
| Kart bilgileri (Stripe) | Yok – Stripe sonrası eklenecek |
| Mesafeli satış onayı | Yok |
| Gizlilik onayı | Yok |
| Fatura bilgisi | Yok |
| Kargo seçenekleri | Tek seçenek |
| Fatura adresi (ayrı) | Yok (teslimat ile aynı sabit) |
| Sipariş özeti | Var |
| Sepete dön linki | Var (eklendi) |

---

*Son güncelleme: Ödeme sayfası kupon, ödeme yöntemi, havale bilgisi ve kart notu ile güncellendi.*

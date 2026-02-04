# Lumina Boutique – E-posta Gönderimi (Sipariş Onay) – Yapılacaklar

Bu dosya, **sipariş onay e-postası (A25)** ve **e-posta ayarları (B38)** için yapılması gereken işleri adım adım listeler. Kod eklemeden önce bu listeyi takip edebilirsiniz.

---

## E-posta gönderimi – Kısa özet (Türkçe)

Sipariş onay e-postası ve e-posta ayarları için yapılması gerekenler:

1. **Admin Ayarlar sayfasına E-posta bölümü eklemek**  
   Gönderen adı/e-posta, sipariş onay e-postası açık/kapalı, SMTP kullanılsın mı, SMTP sunucu/port/kullanıcı/şifre alanlarını form ile kaydedin. Değerler `settings` tablosunda `email` grubu altında tutulacak.

2. **E-posta gönderen bir sınıf (Mailer) yazmak**  
   PHPMailer (veya benzeri) kurup, ayarları `Settings::get('email', ...)` ile okuyan bir Mailer sınıfı yazın. SMTP açıksa SMTP ile, değilse PHP `mail()` ile göndersin. En azından sipariş onayı için `sendOrderConfirmation(alıcı e-posta, müşteri adı, sipariş verisi)` gibi bir metod olsun.

3. **Sipariş onay şablonu**  
   `email_templates` tablosunda `slug = 'order_confirmation'` olan bir kayıt ile konu ve gövde metni tutun. Metinde `{{order_number}}`, `{{customer_name}}`, `{{total}}`, `{{items_list}}` gibi placeholder’lar kullanın; gönderirken bunları gerçek değerlerle değiştirin.

4. **CheckoutController’da sipariş sonrası tetikleme**  
   Sipariş veritabanına yazılıp `commit()` edildikten sonra: `order_confirmation_enabled` ayarı 1 ise müşteri e-postasına (misafir e-postası veya giriş yapmış kullanıcının e-postası) sipariş özeti e-postası gönderin. Gönderim sırasında hata olursa sadece log’a yazın; sipariş işlemini iptal etmeyin.

5. **Güvenlik ve hata yönetimi**  
   SMTP şifresini koda yazmayın; `.env` veya `settings` içinde saklayın. Gönderim hatalarını `storage/logs` veya `error_log` ile kaydedin.

Detaylı adımlar ve tablo açıklamaları aşağıdaki bölümlerdedir.

---

## 1. Amaç

- Sipariş oluşturulduğunda müşteriye **sipariş özeti e-postası** gönderilmek.
- E-posta gönderim ayarları (SMTP veya PHP `mail()`) **panelden** yönetilebilsin.
- İsteğe bağlı: Şablon (konu, metin) panelden veya `email_templates` tablosundan okunabilsin.

---

## 2. Veritabanı / Ayarlar

### 2.1 Settings tablosu (mevcut)

`settings` tablosu zaten var. E-posta için örnek anahtarlar:

| group_name | key | Açıklama |
|------------|-----|----------|
| `email` | `order_confirmation_enabled` | 1 = sipariş onay e-postası açık, 0 = kapalı |
| `email` | `from_email` | Gönderen e-posta (örn. siparis@luminaboutique.com) |
| `email` | `from_name` | Gönderen adı (örn. Lumina Boutique) |
| `email` | `smtp_enabled` | 1 = SMTP kullan, 0 = PHP mail() kullan |
| `email` | `smtp_host` | SMTP sunucu (örn. smtp.gmail.com) |
| `email` | `smtp_port` | SMTP port (587, 465 vb.) |
| `email` | `smtp_encryption` | tls, ssl veya boş |
| `email` | `smtp_username` | SMTP kullanıcı adı |
| `email` | `smtp_password` | SMTP şifre (panelde gösterilirken maskeleme) |

**Yapılacak:** Admin Ayarlar sayfasına **E-posta** bölümü ekleyin; bu anahtarları form ile kaydedin (`App\Helpers\Settings::set('email', 'key', $value)`).

### 2.2 email_templates tablosu (şemada mevcut)

```sql
-- Şemada zaten var:
email_templates (id, slug, name, subject, body_html, body_text, ...)
```

- `slug = 'order_confirmation'` için bir kayıt: sipariş onay e-postası konusu ve gövdesi.
- Placeholder’lar: `{{order_number}}`, `{{customer_name}}`, `{{total}}`, `{{items_list}}` vb. gönderim öncesi replace edilir.

**Yapılacak:** İlk kurulumda bu şablonu seed ile veya panelden ekleyin; mail gönderirken şablonu okuyup placeholder’ları doldurun.

---

## 3. PHP Tarafında Yapılacaklar

### 3.1 E-posta gönderim sınıfı veya fonksiyon

- **Seçenek A – PHP `mail()`:**  
  `mail($to, $subject, $body, $headers)` ile basit gönderim. Sunucu PHP mail yapılandırmasına bağlıdır; genelde SMTP’ye göre daha az güvenilir.

- **Seçenek B – SMTP (önerilen):**  
  PHP’de SMTP için kütüphane kullanın:
  - **PHPMailer** (composer: `phpmailer/phpmailer`) – yaygın ve dokümantasyonu iyi.
  - Veya **Symfony Mailer** (composer) – daha modern.

**Yapılacak:**

1. Composer ile PHPMailer (veya tercih ettiğiniz kütüphane) kurun.
2. Bir **Mailer** helper/servis sınıfı yazın:
   - Ayarları `Settings::get('email', ...)` ile okuyun.
   - `smtp_enabled = 1` ise SMTP ile, değilse `mail()` ile gönderin.
   - Metod örneği: `sendOrderConfirmation(string $toEmail, string $customerName, array $orderData): bool`

### 3.2 Sipariş oluşturma sonrası tetikleme

- **Dosya:** `App\Controllers\Frontend\CheckoutController::store()` (veya siparişi oluşturan metod).
- Sipariş başarıyla kaydedildikten ve `commit()` yapıldıktan sonra:
  1. `Settings::get('email', 'order_confirmation_enabled')` kontrol et; `'1'` değilse e-posta gönderme.
  2. Müşteri e-postasını al: `$guest_email` veya giriş yapmışsa `users.email`.
  3. Sipariş bilgilerini hazırla: sipariş no, toplam, kalemler (ürün adı, adet, fiyat).
  4. Şablonu oku (`email_templates` veya sabit metin); placeholder’ları doldur.
  5. `Mailer::sendOrderConfirmation(...)` çağır.
  6. Hata olursa log’a yazın; sipariş işlemini iptal etmeyin (e-posta başarısız olsa bile sipariş kabul edilmiş olsun).

### 3.3 Güvenlik ve hata yönetimi

- SMTP şifresini `.env` veya `settings` tablosunda saklayın; koda sabit yazmayın.
- Gönderim sırasında exception’ları yakalayıp `error_log` veya `storage/logs` altına yazın.
- Müşteriye “E-posta gönderilemedi” gibi bir mesaj göstermek isteğe bağlı; genelde sessizce log’a yazmak yeterli.

---

## 4. Admin Paneli – E-posta Ayarları (B38)

**Dosya:** `app/Views/admin/settings/index.php` (veya ayrı sekme/bölüm).

**Eklenecek alanlar (form):**

- Sipariş onay e-postası: Açık / Kapalı (checkbox → `order_confirmation_enabled`)
- Gönderen e-posta ve adı (`from_email`, `from_name`)
- SMTP kullan: Evet / Hayır (`smtp_enabled`)
- SMTP sunucu, port, şifreleme (tls/ssl)
- SMTP kullanıcı adı ve şifre (şifre alanı type="password", mevcut değer boş gösterilebilir; kayıtta boşsa eski değeri koruyun)

**Kaydetme:** `SettingsController` içinde POST ile bu alanları alıp `Settings::set('email', 'key', $value)` ile yazın.

---

## 5. Özet Kontrol Listesi

- [ ] Admin Ayarlar’da E-posta bölümü (SMTP, from_email, order_confirmation_enabled vb.)
- [ ] `settings` tablosunda `email` grubu anahtarlarının kaydedilmesi
- [ ] E-posta gönderim sınıfı (PHPMailer veya mail()) – ayarlara göre SMTP / mail()
- [ ] Sipariş onay şablonu (sabit metin veya `email_templates`); placeholder’ların değiştirilmesi
- [ ] CheckoutController’da sipariş kaydı sonrası `order_confirmation_enabled` kontrolü ve mail gönderim çağrısı
- [ ] Hata durumunda log’a yazma; sipariş işleminin iptal edilmemesi
- [ ] (İsteğe bağlı) `email_templates` için seed veya panelden şablon düzenleme

---

## 6. Örnek Akış (Kod Değil, Mantık)

1. Müşteri ödeme formunu doldurup gönderir.
2. CheckoutController siparişi ve order_items’ı veritabanına yazar, stok düşer, commit.
3. `order_confirmation_enabled == 1` mi? Hayır ise bitir.
4. Evet ise: Alıcı e-postası = guest_email veya user email. Sipariş no, toplam, kalem listesi hazırla.
5. Şablonu oku; `{{order_number}}` → gerçek sipariş no, `{{total}}` → toplam tutar vb. değiştir.
6. Mailer ile e-postayı gönder. Exception olursa catch et, log’a yaz; kullanıcıya “Siparişiniz alındı” sayfası normal gösterilir.
7. Kullanıcı yönlendirmesi: `/odeme/tamamlandi` (mevcut davranış).

Bu adımlar uygulandığında sipariş onay e-postası ve e-posta ayarları (B38) tamamlanmış olur.

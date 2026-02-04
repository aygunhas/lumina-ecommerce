# MAMP – Apache ayarları (404 / Not Found çözümü)

`http://localhost:8888/admin/login` adresinde **"Not Found - The requested URL was not found"** alıyorsanız aşağıdakileri kontrol edin.

---

## 1. Document root (kök dizin) mutlaka `public` olsun

MAMP’te sitenin kök dizini **projenin `public` klasörü** olmalı, proje kökü değil.

- **Yanlış:** `.../lumina-ecommerce` (veya `.../htdocs`)
- **Doğru:** `.../lumina-ecommerce/public`

**Nasıl ayarlanır:**

1. MAMP’i açın.
2. **Preferences** (veya **MAMP** → **Preferences**) → **Web Server** sekmesi.
3. **Document Root** alanında **"Select…"** ile `lumina-ecommerce` klasörünü seçmeyin; içindeki **`public`** klasörünü seçin.  
   Örnek tam yol: `/Users/aygunhas/Desktop/lumina-ecommerce/public`
4. **OK** ile kaydedin, MAMP’i durdurup tekrar başlatın.

Bundan sonra site adresi genelde şöyle olur: **http://localhost:8888/** (sonunda `/lumina-ecommerce` olmaz).

---

## 2. mod_rewrite açık olsun

`.htaccess` içindeki kuralların çalışması için Apache **mod_rewrite** modülü açık olmalı.

1. MAMP → **Preferences** → **Web Server**.
2. **Apache** için **"Open"** veya **"Edit"** ile httpd.conf (veya ilgili config) dosyasını açın.
3. Şu satırı bulun:  
   `#LoadModule rewrite_module modules/mod_rewrite.so`  
   Başındaki **`#`** kaldırılıp şöyle olmalı:  
   `LoadModule rewrite_module modules/mod_rewrite.so`
4. Dosyayı kaydedin, Apache’yi yeniden başlatın.

---

## 3. AllowOverride All olsun

`.htaccess` dosyasının okunması için ilgili `<Directory>` bloğunda **AllowOverride All** olmalı.

1. Aynı Apache yapılandırma dosyasında **`AllowOverride`** geçen satırları bulun.
2. Document root için tanımlı `<Directory>` içinde şu olmalı:  
   `AllowOverride All`  
   (Örneğin: `AllowOverride None` ise **None** yerine **All** yazın.)
3. Kaydedip Apache’yi yeniden başlatın.

---

## 4. Adresleri doğru kullanın

- Mağaza anasayfa: **http://localhost:8888/**
- Panel giriş: **http://localhost:8888/admin/login**

Port 8888 yerine 80 veya başka bir port kullanıyorsanız adresi ona göre değiştirin (örn. **http://localhost/admin/login**).

Bu adımlardan sonra `/admin/login` sayfası açılmıyorsa, tarayıcıda **http://localhost:8888/** açılıp açılmadığını da kontrol edin; açılıyorsa document root doğrudur, sorun büyük ihtimalle mod_rewrite veya AllowOverride’dır.

---

## 5. Logo 404 ve includes/layout test sayfası

**Logo 404 (GET /assets/images/lumina-logo.png 404):**  
Sayfada kullanılan `src="/assets/images/lumina-logo.png"` adresi, tarayıcının **document root** altında `/assets/images/` klasörüne bakması demektir. Eğer MAMP’te document root **proje kökü** (`lumina-ecommerce`) ise, sunucu `lumina-ecommerce/assets/` arar; oysa dosyalar **`public/assets/`** içindedir. Bu yüzden 404 alırsınız.

**Çözüm:** Document root’u **`lumina-ecommerce/public`** yapın (yukarıdaki 1. adım). Böylece `http://localhost:8888/assets/images/lumina-logo.png` isteği `public/assets/images/lumina-logo.png` dosyasına gider ve logo yüklenir.

**Test sayfası nereden açılmalı?**  
`includes/layout.php` ile tasarım testi yapıyorsanız, test dosyasını **`public`** içinde tutun ve document root = public iken **http://localhost:8888/test.php** adresinden açın. Proje kökündeki `test.php` dosyası document root = public iken sunucu tarafından servis edilmez; bu yüzden **`public/test.php`** kullanın. Bu sayede hem logo hem favicon doğru yüklenir.

**Mobil menü açılmıyorsa:**  
Header’daki Alpine.js `x-data` kapsayıcısı, hem navbar hem mobil menü panelini içerecek şekilde tek bir div’e alındı; böylece `mobileMenuOpen` mobil menüde de geçerli olur. Document root doğru ve Alpine.js CDN yükleniyorsa mobil menü çalışır.

---

## 6. Tailwind CDN uyarısı (console)

Console’da *"cdn.tailwindcss.com should not be used in production"* uyarısı görünebilir. Bu, Tailwind’in CDN sürümünün **sadece geliştirme** için önerildiği anlamına gelir. Canlı (production) ortamda Tailwind’i **PostCSS eklentisi** veya **Tailwind CLI** ile kurup derlemeniz önerilir. Geliştirme aşamasında bu uyarıyı görmezden gelebilirsiniz; production’a geçerken [Tailwind kurulum dokümanı](https://tailwindcss.com/docs/installation) ile CDN’i kaldırıp build sürecine geçin.

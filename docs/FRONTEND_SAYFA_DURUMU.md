# Frontend sayfa incelemesi – tasarım durumu

## Zaten güncel (Tailwind, font-display, tutarlı dil)

| Sayfa | View | Not |
|-------|------|-----|
| Ana sayfa | home.php + includes (hero, home-categories, featured-products, features-bar) | Tailwind, slider, grid, font-display |
| Kategori listesi | category/show.php | Tailwind, sticky toolbar, sıralama, ürün grid |
| Ürün detay | product/show.php | Tailwind, galeri, varyant, sepete ekle |
| Sepet | cart/index.php | Tailwind, max-w-[1400px], font-display |
| Ödeme | checkout/checkout.php | Tailwind, split, adres seçimi, KVKK |
| Giriş | auth/login.php | Split screen, hero, LUMINA overlay |
| Kayıt | auth/register.php | Split screen, şifre kuralları |
| Şifremi unuttum | auth/forgot-password.php | Split screen, Alpine form/success |
| Şifre sıfırla | auth/reset-password.php | Split screen, şifre gücü |
| Hesabım (tek sayfa) | account/index.php | Tailwind, sekmeler, adres modal, şifre kuralları |

---

## Yenilendi (Tailwind ile güncellendi)

- **İletişim** – `contact/index.php`: Grid, font-display, form/input/buton site dili.
- **Hakkımızda** – `pages/about.php`: Container, font-display, tipografi.
- **Dinamik sayfa** – `pages/show.php`: Container, font-display başlık, içerik alanı.
- **Sipariş tamamlandı** – `checkout/success.php`: Yeşil onay ikonu, font-display, siyah buton.
- **Arama** – `search/index.php`: Kategori sayfası ile aynı ürün grid/kart stili, sıralama/sayfa başına, sayfalama.

---

## Hâlâ eski tasarımda (isteğe bağlı yenileme)

### 6. Hesap – adres formu (ayrı sayfa) – `account/address_form.php`
- **Durum:** Inline style, nav, form alanları.
- **Not:** Ana akış artık account/index.php içinde modal; bu view adres ekle/düzenle GET ile doğrudan açıldığında kullanılıyor.
- **Eksik:** Tailwind, layout ile uyum.

### 7. Hesap – adres sil – `account/address_delete.php`
- **Durum:** Inline style.
- **Not:** Silme artık index içinde modal/onay ile yapılıyor olabilir; controller hâlâ bu view’ı render ediyor.
- **Eksik:** Tailwind, site dili.

### 8. Ödeme (eski form – kullanılmıyor) – `checkout/form.php`
- **Durum:** Inline style, eski ödeme formu.
- **Not:** CheckoutController sadece checkout/checkout.php kullanıyor; form.php muhtemelen kullanılmıyor (dead code).

---

## Özet

- **Yenilendi:** contact, pages/about, pages/show, checkout/success, search/index.
- **İsteğe bağlı:** account/address_form, account/address_delete (akış çoğunlukla hesabım index’te).
- **Kullanılmıyor:** checkout/form.php (CheckoutController sadece checkout/checkout.php kullanıyor).

Bu doküman inceleme ve güncellemeler sonrası güncellendi.

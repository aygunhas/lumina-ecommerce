<?php
$baseUrl = $baseUrl ?? '';
$faqs = [
    // Kargo
    ['q' => 'Siparişim ne zaman kargoya verilir?', 'a' => 'Siparişiniz ödeme onayından sonra 1-2 iş günü içinde kargoya verilir. Havale/EFT ile ödemede bakiye kontrolü tamamlandığında e-posta ile bilgilendirilirsiniz. Kargo takip numaranız aynı e-postada yer alır. Hafta sonu ve resmi tatiller iş gününe dahil değildir.', 'cat' => 'kargo'],
    ['q' => 'Kargo ücreti ne kadar?', 'a' => 'Belirli bir sipariş tutarının üzerindeki alışverişlerde kargo ücretsizdir. Alt tutarlarda kargo ücreti sepet ve ödeme sayfasında gösterilir. Kampanya dönemlerinde ücretsiz kargo limiti değişebilir. Kapıda ödeme seçeneğinde ek bir hizmet bedeli uygulanabilir.', 'cat' => 'kargo'],
    ['q' => 'Siparişimi nasıl takip edebilirim?', 'a' => 'Kargoya verildiğinde e-posta ve SMS ile takip numarası gönderilir. "Hesabım > Siparişlerim" bölümünden siparişinize tıklayarak kargo firmasının sayfasına yönlendirilebilir veya takip numarası ile kargo firmasının web sitesinden anlık takip yapabilirsiniz.', 'cat' => 'kargo'],
    ['q' => 'Teslimat süresi ne kadar?', 'a' => 'Kargoya verimden itibaren Türkiye genelinde ortalama 1-3 iş günü içinde teslimat yapılır. İl ve bölgeye göre süre 4-5 iş gününe kadar uzayabilir. Adrese teslimatta bulunamama durumunda kargo firması ikinci teslimat denemesi yapar; iletişim bilgilerinizin güncel olması önemlidir.', 'cat' => 'kargo'],
    ['q' => 'Yurtdışına kargo gönderiyor musunuz?', 'a' => 'Şu an sadece Türkiye içi teslimat yapıyoruz. Yurtdışı gönderi planlarımız üzerinde çalışıyoruz; duyuruları bülten ve sosyal medya hesaplarımızdan takip edebilirsiniz.', 'cat' => 'kargo'],
    ['q' => 'Siparişimi nasıl iptal edebilirim?', 'a' => 'Siparişiniz kargoya verilmeden önce iptal edebilirsiniz. "Hesabım > Siparişlerim" üzerinden ilgili siparişte "İptal talebi" butonunu kullanın veya müşteri hizmetlerimizle iletişime geçin. Kargoya verildikten sonra iptal yerine teslim alıp iade sürecini başlatmanız gerekir.', 'cat' => 'kargo'],
    ['q' => 'Kapıda bırakma (contactless) seçeneği var mı?', 'a' => 'Evet. Teslimat sırasında kapıda bırakma talep edebilirsiniz; kargo görevlisi ürünü belirttiğiniz yere bırakıp fotoğrafla teslimat onayı alabilir. Bu seçenek ödeme ekranında veya kargo firması ile iletişimde belirtilebilir.', 'cat' => 'kargo'],
    // İade
    ['q' => 'İade veya değişim nasıl yapılır?', 'a' => 'Ürünü teslim aldığınız tarihten itibaren 14 gün içinde, etiketli ve kullanılmamış ürünleri iade edebilir veya beden/renk değişimi talep edebilirsiniz. "Hesabım > Siparişlerim" üzerinden "İade/değişim başlat" ile süreci başlatın; iade kargo etiketini yazdırıp paketi kargoya verin. Onay sonrası iade veya yeni ürün süreci tamamlanır.', 'cat' => 'iade'],
    ['q' => 'İade kargo ücreti benden alınır mı?', 'a' => 'Ürün hatası, yanlış ürün veya yanlış beden gönderimi gibi mağaza kaynaklı durumlarda iade kargo ücreti Lumina tarafından karşılanır. Müşteri kaynaklı iadelerde (beğenmeme, yanlış sipariş vb.) kargo ücreti alıcıya aittir; anlaşmalı kargo ile indirimli iade yapabilirsiniz.', 'cat' => 'iade'],
    ['q' => 'İade süresi ne kadar?', 'a' => 'Ürünü teslim aldığınız tarihten itibaren 14 gün yasal cayma hakkınız vardır. İade paketini bu süre içinde kargoya vermeniz yeterlidir. Paket bize ulaştıktan sonra inceleme 3-5 iş günü sürer; uygunsa iade tutarı 10 iş günü içinde ödeme yönteminize iade edilir.', 'cat' => 'iade'],
    ['q' => 'Hangi ürünler iade edilemez?', 'a' => 'Kişiye özel üretilen veya hijyen gereği kullanılmış sayılabilecek ürünler (iç çamaşırı, mayo, çorap vb. açılmış paketler) iade kapsamı dışındadır. Aksesuar ve ayakkabılar etiketli ve kullanılmamış ise iade edilebilir. Detaylı liste "İade ve Değişim Koşulları" sayfasında yer alır.', 'cat' => 'iade'],
    ['q' => 'Hediye olarak gelen ürünü iade edebilir miyim?', 'a' => 'Evet. Hediye alan kişi, ürünü 14 gün içinde iade edebilir. İade sonrası ödeme, orijinal siparişi veren kişinin kullandığı ödeme yöntemine iade edilir. Hediye çeki veya hediye kartı ile yapılan alışverişlerde iade tutarı hediye çeki olarak verilebilir.', 'cat' => 'iade'],
    ['q' => 'Değişim için yeni ürün ne zaman gönderilir?', 'a' => 'Beden veya renk değişimi talebinde, mevcut ürününüzün kargoya verildiği ve bize ulaştığı onaylandıktan sonra yeni ürün aynı gün veya ertesi iş günü kargoya verilir. Stok yoksa alternatif renk/beden önerilir veya iade işlemi tamamlanır.', 'cat' => 'iade'],
    // Ödeme
    ['q' => 'Hangi ödeme yöntemlerini kabul ediyorsunuz?', 'a' => 'Kredi kartı ve banka kartı (tek çekim ve taksit), havale/EFT, kapıda ödeme (nakit veya kart) kabul ediyoruz. Tüm kart işlemleri 3D Secure ile güvence altındadır. Ödeme sayfasında seçtiğiniz bankaya göre taksit seçenekleri listelenir.', 'cat' => 'odeme'],
    ['q' => 'Taksit imkanı var mı?', 'a' => 'Evet. Kredi kartı ile belirli tutarın üzerindeki alışverişlerde bankaya göre 2, 3, 6, 9 veya 12 taksit seçenekleri sunulur. Kampanya dönemlerinde taksit sayısı artabilir. Taksit bilgisi ödeme ekranında kartınızı seçtikten sonra görüntülenir.', 'cat' => 'odeme'],
    ['q' => 'Ödeme güvenli mi?', 'a' => 'Evet. Ödemeler SSL şifreli bağlantı ve 3D Secure doğrulaması ile işlenir. Kart bilgileriniz saklanmaz; işlem banka ve ödeme altyapı sağlayıcımız üzerinden güvenli şekilde gerçekleştirilir. Dolandırıcılık önlemleri kapsamında bazı siparişlerde ek doğrulama istenebilir.', 'cat' => 'odeme'],
    ['q' => 'Fatura nasıl kesilir?', 'a' => 'Sipariş tamamlandıktan sonra e-fatura veya e-arşiv fatura e-posta adresinize iletilir. Kurumsal fatura (vergi no, adres) için sipariş notunda belirtebilir veya müşteri hizmetleri ile iletişime geçebilirsiniz. Faturayı "Hesabım > Siparişlerim" üzerinden de indirebilirsiniz.', 'cat' => 'odeme'],
    ['q' => 'Havale/EFT ile nasıl öderim?', 'a' => 'Ödeme sayfasında "Havale/EFT" seçeneğini seçin; sipariş onaylandıktan sonra size banka hesap bilgilerimiz ve açıklama satırında kullanmanız gereken sipariş kodu gösterilir. Ödemeyi 24 saat içinde yapmanız gerekir; aksi halde sipariş iptal edilir. Dekontu iletebilirsiniz.', 'cat' => 'odeme'],
    ['q' => 'Kapıda ödeme yapabilir miyim?', 'a' => 'Evet. Teslimat sırasında nakit veya kredi/banka kartı ile ödeme yapabilirsiniz. Kapıda ödeme seçeneğinde bazı bölgelerde ek hizmet bedeli uygulanabilir; tutar sipariş özetinde gösterilir.', 'cat' => 'odeme'],
    // Ürün / Beden / Genel
    ['q' => 'Beden tablosu nerede?', 'a' => 'Her ürün sayfasında "Beden rehberi" veya "Beden tablosu" linki bulunur. Genel beden tablomuz footer\'daki "Yardım" bölümünde ve ilgili ürün sayfalarında yer alır. Kadın, erkek ve aksesuar için ayrı tablolar sunulur; cm ve uluslararası beden karşılıkları belirtilir.', 'cat' => 'genel'],
    ['q' => 'Ürün bedeni küçük/büyük gelirse ne yapmalıyım?', 'a' => '14 gün içinde ücretsiz beden değişimi yapabilirsiniz. "Hesabım > Siparişlerim" üzerinden "Değişim talebi" ile aynı ürünün farklı bedenini talep edin; stok varsa yeni beden kargoya verilir. Ürün sayfasındaki beden rehberi ve müşteri yorumları da doğru beden seçimine yardımcı olur.', 'cat' => 'genel'],
    ['q' => 'Ürünlerin kumaş ve bakım bilgisi nerede?', 'a' => 'Her ürün sayfasında "Ürün detayları" veya "Bakım bilgisi" bölümünde kumaş içeriği (ör. %100 pamuk, viskon), bakım talimatları (yıkama, ütü, kurutma) ve ürün özellikleri yer alır. Etiketlerde de aynı bilgiler bulunur.', 'cat' => 'genel'],
    ['q' => 'Stokta olmayan ürün ne zaman gelir?', 'a' => 'Stok girişleri düzenli güncellenir. "Stokta yok" olan ürünlerde "Geldiğinde haber ver" seçeneği varsa e-posta ile bilgilendirilirsiniz. Belirli bir ürün için tahmini stok tarihi müşteri hizmetlerinden sorulabilir.', 'cat' => 'genel'],
    ['q' => 'Hediye paketi ve notu ekleyebilir miyim?', 'a' => 'Evet. Sepette veya ödeme sayfasında "Hediye paketi" ve "Hediye notu" seçenekleri sunulur. Hediye notu kutu içine eklenir; fiyat bilgisi gösterilmez. Kurumsal veya toplu hediye talepleri için müşteri hizmetlerimizle iletişime geçebilirsiniz.', 'cat' => 'genel'],
    ['q' => 'İndirim kodu nasıl kullanılır?', 'a' => 'Sepet sayfasında "İndirim kodu" veya "Kupon kodu" alanına kodu girip uygulayın. Kod geçerliyse indirim sepet özetine yansır. Her kuponun kullanım koşulları (minimum tutar, kategoriler, tek kullanım vb.) farklı olabilir; geçersiz kodda uyarı mesajı görüntülenir.', 'cat' => 'genel'],
    ['q' => 'Hesap oluşturmak zorunlu mu?', 'a' => 'Hayır. Misafir olarak sipariş verebilirsiniz; sadece teslimat ve iletişim bilgilerinizi girmeniz yeterlidir. Hesap oluşturursanız sipariş geçmişi, adresler, favoriler ve daha hızlı alışveriş imkanından yararlanırsınız.', 'cat' => 'genel'],
    ['q' => 'Şifremi unuttum, nasıl sıfırlarım?', 'a' => 'Giriş sayfasında "Şifremi unuttum" linkine tıklayın; e-posta adresinizi girin. Size gelen link ile şifrenizi sıfırlayabilirsiniz. Link belirli bir süre geçerlidir; gelmezse spam klasörünü kontrol edin veya müşteri hizmetleri ile iletişime geçin.', 'cat' => 'genel'],
    ['q' => 'Mağazanız var mı, deneyebilir miyim?', 'a' => 'Evet. İstanbul\'daki flagship mağazamızda koleksiyonları inceleyebilir, deneyebilir ve satın alabilirsiniz. Adres ve çalışma saatleri "İletişim" sayfasında yer alır. Özel günlerde randevu ile kişisel alışveriş danışmanlığı da sunulmaktadır.', 'cat' => 'genel'],
];
?>
<script type="application/json" id="faq-data"><?= json_encode($faqs, JSON_UNESCAPED_UNICODE) ?></script>
<div x-data="faqPage()" x-effect="activeCat; searchQuery; active = null">
    <!-- Header ve Arama -->
    <section class="bg-gray-50 py-20 text-center">
        <h1 class="font-display text-2xl tracking-widest mb-8 text-primary">NASIL YARDIMCI OLABİLİRİZ?</h1>
        <div class="max-w-xl mx-auto relative px-4">
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </span>
                <input
                    type="search"
                    placeholder="Kargo, iade veya beden hakkında arayın..."
                    class="w-full border border-gray-200 rounded-full py-4 pl-12 pr-6 focus:border-black focus:ring-0 shadow-sm text-sm"
                    x-model="searchQuery"
                    aria-label="Yardım arama"
                >
            </div>
        </div>
    </section>

    <!-- Kategoriler (yatay kaydırılabilir) -->
    <div class="border-b border-gray-200 bg-white sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-4 py-4 overflow-x-auto" style="scrollbar-width: none; -ms-overflow-style: none;">
            <div class="flex gap-2 justify-center min-w-max pb-1">
                <button type="button" @click="activeCat = 'all'" :class="activeCat === 'all' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-full text-xs font-medium whitespace-nowrap transition">Tümü</button>
                <button type="button" @click="activeCat = 'kargo'" :class="activeCat === 'kargo' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-full text-xs font-medium whitespace-nowrap transition">Kargo</button>
                <button type="button" @click="activeCat = 'iade'" :class="activeCat === 'iade' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-full text-xs font-medium whitespace-nowrap transition">İade</button>
                <button type="button" @click="activeCat = 'odeme'" :class="activeCat === 'odeme' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-4 py-2 rounded-full text-xs font-medium whitespace-nowrap transition">Ödeme</button>
            </div>
        </div>
    </div>

    <!-- Akordiyon -->
    <div class="max-w-2xl mx-auto py-16 space-y-4 px-4">
        <template x-for="(faq, index) in filteredFaqs" :key="index">
            <div class="border border-gray-200 rounded-sm overflow-hidden">
                <button type="button"
                        @click="active === index ? active = null : active = index"
                        class="w-full flex justify-between items-center p-5 text-left hover:bg-gray-50 transition">
                    <span class="font-medium text-sm text-primary pr-4" x-text="faq.q"></span>
                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full border border-gray-300 text-gray-500 text-lg leading-none" x-text="active === index ? '−' : '+'"></span>
                </button>
                <div x-show="active === index"
                     x-collapse
                     x-cloak
                     class="p-5 pt-0 text-sm text-gray-500 leading-relaxed border-t border-gray-100">
                    <p x-text="faq.a"></p>
                </div>
            </div>
        </template>
        <p x-show="filteredFaqs.length === 0"
           x-cloak
           class="text-center text-gray-500 text-sm py-8">Aramanızla eşleşen soru bulunamadı.</p>
    </div>
</div>
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('faqPage', function() {
        var el = document.getElementById('faq-data');
        var faqs = el ? JSON.parse(el.textContent) : [];
        return {
            active: null,
            searchQuery: '',
            activeCat: 'all',
            faqs: faqs,
            get filteredFaqs() {
                var self = this;
                return this.faqs.filter(function(f) {
                    var matchCat = self.activeCat === 'all' || f.cat === self.activeCat;
                    if (!matchCat) return false;
                    if (!self.searchQuery.trim()) return true;
                    var term = self.searchQuery.toLowerCase();
                    return (f.q + ' ' + f.a).toLowerCase().includes(term);
                });
            }
        };
    });
});
</script>

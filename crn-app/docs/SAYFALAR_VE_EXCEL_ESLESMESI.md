# CRN Baca – Sayfalar ve Excel Eşleşmesi

Bu doküman, uygulama sayfalarının **örnek- 26.01.2026.xlsx** dosyasındaki hangi işlevlere karşılık geldiğini açıklar.

---

## Excel dosyasındaki sayfalar (özet)

| Excel sayfası        | İşlevi |
|----------------------|--------|
| **BAYİ**             | Bayi / müşteri firmaları (ünvan, il/ilçe, mail, tel, sevk adresi vb.) |
| **LİSTE**            | Ürün listesi (malzeme kodu, açıklama, birim, sac/izole/430 fiyatları, liste fiyatı) |
| **SatışFiyat**       | Satış fiyatları (malzeme kodu ile LİSTE ile uyumlu) |
| **genel hesap**      | Maliyet / fiyat formüllerinde kullanılan sabitler (KDV, işçilik vb.) |
| **TEKLİF HAZIRLAMA** | Teklif no, müşteri, tarih, teklif kalemleri |
| **SiparişFormu**     | Sipariş no, siparişi veren firma, proje/cihaz, kalem tablosu, iskonto, KDV, toplam |

---

## Admin paneli sayfaları

### 1. Bayiler (Dealers)

- **Yol:** `/admin/dealers`
- **Ne yapar:** Bayi / müşteri firmalarını ekleme, düzenleme, listeleme.
- **Excel eşleşmesi:** **BAYİ** sayfası ve **Sipariş Formu** başlığındaki “SİPARİŞİ VEREN FİRMA” bilgileri.
- **Alanlar:** Firma no, ünvan, adres, il/ilçe, ilgili kişi, tel, mail, sevk adresi. Teklif ve siparişte “Bayi / Müşteri” seçimi bu kayıtlara gider.

---

### 2. Ürünler (Products)

- **Yol:** `/admin/products`
- **Ne yapar:** Malzeme (ürün) tanımları ve birim fiyatlar; teklif/sipariş kalemlerinde kullanılır.
- **Excel eşleşmesi:** **LİSTE** ve **SatışFiyat** sayfaları (malzeme kodu, malzeme açıklaması, birim, sac/izole/430 fiyatları, **fiyat liste**).
- **Alanlar:** Malzeme kodu, malzeme açıklaması, uzunluk, sac kalınlık, birim kilo, birim, sac/izole/430 fiyatları, fiyat liste, aktif. Teklif/siparişte ürün seçilince varsayılan birim fiyat “Fiyat Liste”den gelir.

---

### 3. Maliyet Parametreleri (Cost Params)

- **Yol:** `/admin/cost-params`
- **Ne yapar:** Excel “genel hesap” sayfasındaki gibi maliyet/fiyat formüllerinde kullanılan **sabit değerleri** tutar (sac fiyatı, maşon fiyatı, KDV oranı vb.). Kodda `CostParam::getByKey('sac_fiyati')` ile bu değer okunur.
- **Excel eşleşmesi:** **genel hesap** sayfasındaki sabitler (ör. sac fiyatı kg, maşon fiyatı, tutacak fiyatı, KDV oranı).
- **Alanlar:**
  - **Görünen ad:** Ekranda gördüğünüz isim (örn. “SAC FİYATI”).
  - **Parametre kodu (key):** Kodda ve formüllerde kullanılan **benzersiz kod**. Küçük harf, rakam ve alt çizgi (örn. `sac_fiyati`, `kdv_orani`). Kayıt sonrası değiştirilmemeli; aksi halde o kodu kullanan hesaplar bozulur.
  - **Değer:** Sayısal değer (örn. 6.5, 18).
  - **Birim:** İsteğe bağlı birim (TL/kg, % vb.).
- **Kullanım:** Hesaplama yapan kod bu parametreyi `CostParam::getByKey('parametre_kodu')` ile okur; örn. `CostParam::getByKey('sac_fiyati')` → 6.5.

---

### 4. Teklifler (Quotes)

- **Yol:** `/admin/quotes`
- **Ne yapar:** Teklif oluşturma, düzenleme, listeleme; tek tek Excel/CSV/PDF dışa aktarma; liste dışa aktar; teklif kalemlerini içe aktarma.
- **Excel eşleşmesi:** **TEKLİF HAZIRLAMA** ve **Sipariş Formu** yapısı (başlık + kalem tablosu).
- **Başlık bilgileri:** Teklif no, bayi (siparişi veren firma), durum (taslak / gönderildi / onaylandı / reddedildi).
- **Kalemler:** Ürün seçimi (malzeme kodu/açıklama), birim fiyat, adet; tutar otomatik. Excel’deki “MALZEME KODU, AÇIKLAMA, BİRİM, BİRİM FİYAT, ADET, TUTAR” yapısına uyumludur.
- **Export:** Teklif görüntülemeden Excel/CSV/PDF; listeden toplu CSV/XLSX.

---

### 5. Siparişler (Orders)

- **Yol:** `/admin/orders`
- **Ne yapar:** Sipariş oluşturma, düzenleme, listeleme; tek tek Excel/CSV/PDF dışa aktarma; liste dışa aktar; sipariş kalemlerini içe aktarma.
- **Excel eşleşmesi:** **SiparişFormu** sayfasının tamamı.
- **Başlık bilgileri:** Sipariş no, bayi, isteğe bağlı teklif referansı, sipariş tarihi, proje adı, cihaz marka/model, durum, iskonto %, açıklama.
- **Kalemler:** Ürün, birim fiyat, adet; tutar otomatik. Excel’deki kalem tablosu (malzeme kodu, açıklama, birim, birim fiyat, adet, tutar) ile uyumludur.
- **Export:** Sipariş görüntülemeden Excel/CSV/PDF; listeden toplu CSV/XLSX.

---

### 6. Kullanıcılar (Users)

- **Yol:** `/admin/users`
- **Ne yapar:** Admin veya müşteri paneli girişi yapacak kullanıcıları ve rollerini yönetir.
- **Excel eşleşmesi:** Doğrudan Excel’de yok; panel güvenliği ve yetkilendirme için.
- **Roller:** Admin / Satış → admin paneline giriş; Müşteri → müşteri paneline giriş (bayi atanmalı). Müşteri kullanıcıları sadece kendi bayisine bağlı teklifleri görür.

---

## Müşteri paneli sayfaları

### 7. Tekliflerim (Müşteri – Quotes)

- **Yol:** `/musteri/quotes`
- **Ne yapar:** Müşteri girişi yapan kullanıcı, kendi bayisine ait teklifleri listeler; teklifi görüntüleyip onaylayabilir veya reddedebilir (isteğe bağlı indirim/açıklama ile).
- **Excel eşleşmesi:** **TEKLİF HAZIRLAMA** sürecinin müşteri tarafı (teklifi görme ve onay/red). Excel’de bu adım manuel; uygulamada panel üzerinden yapılır.

---

## Özet tablo

| Uygulama sayfası   | Excel sayfası / işlevi |
|-------------------|------------------------|
| Bayiler           | BAYİ, Sipariş Formu (firma bilgileri) |
| Ürünler           | LİSTE, SatışFiyat |
| Maliyet Parametreleri | genel hesap |
| Teklifler (admin) | TEKLİF HAZIRLAMA, Sipariş Formu (başlık + kalemler) |
| Siparişler (admin) | SiparişFormu (tümü) |
| Kullanıcılar      | — (panel girişi) |
| Tekliflerim (müşteri) | TEKLİF HAZIRLAMA (müşteri onayı) |

---

## Çeviriler ve form metinleri

- **filament-forms::components.select.no_options_message** gibi anahtarlar Türkçe çeviri dosyalarına eklendi; Select alanlarında “Seçenek bulunamadı.” metni görünür.
- Form bölümlerinde **description** ile Excel’deki hangi yapıya karşılık geldiği kısaca açıklandı; gereken yerlerde **noOptionsMessage** ve **helperText** ile kullanım rehberi verildi.

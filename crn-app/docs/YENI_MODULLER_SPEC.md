# CRN Admin Panel – Yeni Modüller Spesifikasyonu

Bu doküman, admin paneline Notion benzeri tablo/görünümlerle eklenecek tüm modülleri ve alanlarını tanımlar.

---

## Özet – Eklenecek Modüller

| # | Modül | Nav Grubu | Açıklama |
|---|-------|-----------|----------|
| 1 | **Puantaj** | İnsan Kaynakları | Personel günlük devam takibi |
| 2 | **Personel İletişim** | İnsan Kaynakları | Personel iletişim ve acil durum bilgileri |
| 3 | **Üretim Hattı Makine Takibi** | Üretim | Makine listesi ve bakım takvimi |
| 4 | **Üretim Planlama** | Üretim | Kanban / sipariş durum panosu |
| 5 | **Günlük Ciro Takibi** | Satış/Finans | Hedef vs gerçekleşen ciro |
| 6 | **Hırdavat Sarf Malzeme Stok** | Stok | Sarf malzeme envanter takibi |
| 7 | **Yapılacaklar** | Genel | Görev ve proje takibi |

---

## 1. Puantaj (Attendance) – ÖNCELİKLİ

**Hedef:** Personellerin günlük devam durumunu (tam gün, yarım gün, gelmedi) ve gelmeme sebebini takip etmek.

### Alanlar

| Alan | Tip | Zorunlu | Açıklama |
|------|-----|---------|----------|
| **personel_id** | FK → Personel | Evet | Personel |
| **tarih** | Date | Evet | Puantaj tarihi |
| **durum** | Select | Evet | Tam Gün / Yarım Gün / Gelmedi |
| **aciklama** | Text | Hayır | Neden gelmediği (gelmedi ise) |
| **notlar** | Textarea | Hayır | Ek notlar |
| **giris_saati** | Time | Hayır | Giriş saati (opsiyonel) |
| **cikis_saati** | Time | Hayır | Çıkış saati (opsiyonel) |

### Durum seçenekleri

- `tam_gun` → Tam Gün
- `yarim_gun` → Yarım Gün
- `gelmedi` → Gelmedi

### Görünümler / Sekmeler

- **Tüm Kayıtlar** – Liste tablosu
- **Tarihe Göre** – Tarih filtreli
- **Personel Bazlı** – Personel seçip o kişinin puantaj geçmişi
- **Gelmediler** – Sadece “gelmedi” kayıtları

---

## 2. Personel İletişim Bilgileri

**Hedef:** Personellerin iletişim ve acil durum bilgilerini merkezi tutmak. (Puantaj modülünde `personel_id` bu tabloya bağlanır.)

### Alanlar

| Alan | Tip | Zorunlu | Açıklama |
|------|-----|---------|----------|
| **ad_soyad** | Text | Evet | Ad Soyad |
| **departman** | Select/Text | Hayır | Departman |
| **pozisyon** | Text | Hayır | Pozisyon |
| **telefon** | Text | Hayır | Telefon |
| **email** | Email | Hayır | E-posta |
| **evli** | Boolean | Hayır | Evli mi |
| **dogum_yeri** | Text | Hayır | Doğum Yeri |
| **acil_durum_kisi** | Text | Hayır | Acil Durum Kişisi |
| **acil_durum_telefonu** | Text | Hayır | Acil Durum Telefonu |
| **kan_grubu** | Select | Hayır | Kan Grubu (A+, B+, vb.) |
| **aktif** | Boolean | Evet | Aktif personel |

### Görünümler

- Tüm Personel
- Departmanlara Göre
- Hızlı İletişim (özet kart)
- Acil Durum Bilgileri

---

## 3. Üretim Hattı Makine Takibi

**Hedef:** Üretim hatlarındaki makineleri ve bakım planlarını takip etmek.

### Alanlar

| Alan | Tip | Zorunlu | Açıklama |
|------|-----|---------|----------|
| **makine_adi** | Text | Evet | Makine Adı |
| **makine_kodu** | Text | Hayır | Makine Kodu (barkod) |
| **durum** | Select | Evet | Çalışıyor / Arızalı / Bakımda / Kapalı |
| **uretim_hatti** | Select/Text | Hayır | Üretim Hattı (HAT1, HAT2 vb.) |
| **son_bakim_tarihi** | Date | Hayır | Son Bakım Tarihi |
| **sonraki_bakim_tarihi** | Date | Hayır | Sonraki Bakım Tarihi |
| **bakim_sorumlusu** | Text/FK | Hayır | Bakım Sorumlusu |
| **bakim_turu** | Select | Hayır | Periyodik / Arıza / Revizyon |
| **notlar** | Textarea | Hayır | Notlar |

### Görünümler

- Tüm Makineler
- Bakım Takvimi (takvim görünümü)
- Durum Panosu (özet widget)
- Bakım Gereken Makineler
- Detaylı Liste

---

## 4. Üretim Planlama (Kanban + Detay Tablo)

**Hedef:** Siparişlerin üretim ve sevk aşamalarını Kanban ve tablo ile takip etmek.

### Sevkiyata Göre Durum (Kanban sütunları)

- Kendi Aldı
- Otogardan Gidecek
- Kendi Alacak
- Planlanmadı
- Kamyon Bekleniyor
- Sevk Edildi

### Üretim Durumuna Göre (Kanban sütunları)

- Planlanan
- Üretimde
- Üretim Devam Edecek
- Depoda Sevkiyata Hazır
- Sevk Edildi
- Otogardan Gönderildi

### Detay Tablo Alanları (Tüm Üretimler)

| Alan | Tip | Açıklama |
|------|-----|----------|
| **proje_adi** | Text | Proje/Sipariş kısa adı |
| **siparis_tarihi** | Date | Sipariş tarihi |
| **planlanan_tarih** | Date | Planlanan tarih |
| **bayi_adi** | FK → Dealer | Bayi adı |
| **proje_siparis_adi** | Text | Proje / Sipariş adı |
| **ek_bilgi** | Text | Ek bilgi |
| **durum** | Select | Üretim/sevkiyat durumu |
| **oncelik** | Select | Acil / Orta / Beklemede |
| **tahmini_hazirlik** | Select | Öğleden Önce / Öğleden Sonra |
| **hat** | Select | HAT1, HAT2 vb. |
| **baslama_tarihi** | Date | Başlama |
| **bitis_tarihi** | Date | Bitiş |
| **kontrol** | Select | Edildi / Beklemede |
| **nakliye** | Select | Bize Ait / Alıcıya Ait / Kendi Alacak |
| **sevkiyat_durumu** | Select | Sevk edildi / Kamyon bekleniyor vb. |
| **notlar** | Textarea | Notlar |

*Not: Bu modül mevcut `Order` modeliyle entegre edilebilir veya ayrı `ProductionPlan` tablosu oluşturulabilir.*

---

## 5. Günlük Ciro Takibi

**Hedef:** Günlük hedef ve gerçekleşen ciroyu karşılaştırmak.

### Alanlar

| Alan | Tip | Zorunlu | Açıklama |
|------|-----|---------|----------|
| **tarih** | Date | Evet | Tarih |
| **hedeflenen_ciro** | Decimal | Hayır | Hedeflenen ciro (TL) |
| **gerceklesen_ciro** | Decimal | Hayır | Gerçekleşen ciro (TL) |
| **fark** | Decimal (hesaplanan) | – | Gerçekleşen - Hedef |
| **durum** | Select | Evet | Hedef Aşıldı / Hedef Tutturuldu / Beklemede |
| **notlar** | Text | Hayır | Notlar |
| **ay** | Select/Text | Hayır | Ay (Şubat, Mart vb. – otomatik doldurulabilir) |

### Görünümler

- Tüm Kayıtlar
- Durum Tahtası
- Hedef Aşılanlar

---

## 6. Hırdavat Sarf Malzeme Stok Takip

**Hedef:** Sarf malzeme stoklarını ve sipariş eşiklerini takip etmek.

### Alanlar

| Alan | Tip | Zorunlu | Açıklama |
|------|-----|---------|----------|
| **malzeme** | Text | Evet | Malzeme adı |
| **kategori** | Select/Text | Hayır | Kategori |
| **mevcut_stok** | Decimal | Evet | Mevcut stok adedi |
| **toptanci** | Text | Hayır | Toptancı/Tedarikçi |
| **siparis_min_stok** | Decimal | Hayır | Sipariş için min. stok eşiği |
| **son_siparis_tarihi** | Date | Hayır | Son sipariş tarihi |
| **konum** | Text | Hayır | Depo konumu |
| **notlar** | Textarea | Hayır | Notlar |
| **yeniden_siparis_durumu** | Select/ Badge | – | Mevcut < min ise “Sipariş Gerekli” |

### Görünümler

- Tüm Envanter
- Yeni Verilmiş Siparişler
- Hırdavat Sarf (kategori filtresi)
- Sipariş Gerekenler

---

## 7. Yapılacaklar Listesi

**Hedef:** Görev ve proje takibi (Notion benzeri).

### Alanlar

| Alan | Tip | Zorunlu | Açıklama |
|------|-----|---------|----------|
| **yapilacak** | Text | Evet | Görev başlığı |
| **durum** | Select | Evet | Başlamadı / Devam Ediyor / Tamamlandı / Eksikler Not Edildi |
| **takip_edecek** | Select/Text | Hayır | Sorumlu (Muhasebe, Mühendislik, Montaj vb.) |
| **kadar_tarih** | Date | Hayır | Teslim/Bitiş tarihi |
| **oncelik** | Select | Hayır | Acil / Orta / Beklemede / Takipte Devam |
| **aciklama** | Textarea | Hayır | Açıklama |
| **gerceklesme_tarihi** | Date | Hayır | Tamamlanma tarihi |
| **dosya_eki** | File | Hayır | Dosya eki |
| **notlar** | Textarea | Hayır | Notlar |
| **guncelleme** | Timestamp | – | Son güncelleme (otomatik) |

### Görünümler

- All Tasks (Tüm Görevler)
- By Status (Duruma göre)
- My Tasks (Benim görevlerim)
- Checklist

---

## Uygulama Sırası Önerisi

1. **Personel** modeli – Puantaj ve Personel İletişim için temel
2. **Puantaj** – İstenen en öncelikli modül
3. **Personel İletişim**
4. **Üretim Planlama** – Mevcut Order ile entegrasyon
5. **Üretim Hattı Makine Takibi**
6. **Günlük Ciro Takibi**
7. **Hırdavat Sarf Stok**
8. **Yapılacaklar**

---

## Teknik Notlar

- Tüm modüller Filament Resource olarak `app/Filament/Resources/` altında oluşturulacak
- Navigation grupları: **İnsan Kaynakları**, **Üretim**, **Satış**, **Stok**, **Genel**
- Tablo görünümleri: Filament Table + Filters + Tabs
- Kanban için: Filament’in `RelationManager` + custom view veya `filament/spatie-laravel-tags` / benzeri paket
- Notion tarzı esnek alanlar için: Her modül kendi migration’ında tanımlanacak; ileride JSON column ile ek alan desteği eklenebilir

# Maliyet Parametreleri ve Ürünler – CSV / Excel İçe Aktarma

Admin panelde **Maliyet Parametreleri** ve **Ürünler** listelerinde **İçe Aktar (CSV/Excel)** ile toplu yükleme yapılır. Filament, sütun eşlemesini dosya başlıklarına göre otomatik önerir; gerekirse elle düzeltirsiniz.

Desteklenen formatlar: **.csv**, **.xlsx**, **.xls** (Filament import).

---

## 1. Maliyet parametreleri (`cost_params`)

Aynı **`key`** (parametre kodu) varsa kayıt **güncellenir**, yoksa **yeni** oluşturulur.

### Sütunlar (önerilen başlık satırı)

| Sütun adı (İngilizce) | Alternatif başlıklar | Zorunlu | Açıklama |
|------------------------|----------------------|---------|----------|
| `name` | Görünen ad, gorunen_ad | Evet | Ekranda görünen isim (örn. SAC FİYATI) |
| `key` | parametre_kodu, kod | Evet | Kodda kullanılan benzersiz anahtar: küçük harf, rakam, alt çizgi (örn. `sac_fiyati`) |
| `value` | deger, Değer | Evet | Sayısal değer |
| `unit` | birim, Birim | Hayır | Örn. `TL/kg`, `%` |

### Örnek CSV (UTF-8, virgül ayırıcı)

```csv
name,key,value,unit
SAC FİYATI,sac_fiyati,6.5,TL/kg
KDV ORANI,kdv_orani,20,%
MAŞON FİYATI,mason_fiyati,12.5,
```

### Örnek Excel

İlk satır başlık, aşağıdaki satırlar veri. Başlıklar yukarıdaki tablodaki isimlerden biri olabilir (Türkçe/İngilizce tahminler desteklenir).

**Not:** `key` değiştirmeden bırakılmalıdır; uygulama içi `CostParam::getByKey('...')` bu anahtara bağlıdır.

---

## 2. Ürünler (`products`)

Aynı **`malzeme_kodu`** varsa kayıt **güncellenir**, yoksa **yeni** oluşturulur. Boş bırakılan opsiyonel sütunlar, güncellemede mevcut değeri **değiştirmez** (sütun eşlenmemiş veya hücre boşsa).

### Sütunlar

| Sütun adı | Zorunlu | Açıklama |
|-----------|---------|----------|
| `malzeme_kodu` | Evet | Benzersiz malzeme kodu |
| `malzeme_aciklamasi` | Evet | Açıklama |
| `uzunluk_m` | Hayır | Metre |
| `sac_kalinlik` | Hayır | Sac kalınlık |
| `birim_kilo` | Hayır | Birim kilo |
| `birim` | Hayır | Örn. AD, M, KG (varsayılan: AD) |
| `sac_fiyati` | Hayır | |
| `izole_fiyati` | Hayır | |
| `kilif_430_fiyati` | Hayır | |
| `fiyat_liste` | Hayır | Liste satış fiyatı |
| `aktif` | Hayır | `1`, `0`, `evet`, `hayır`, `true`, `false` vb. Boş: yeni kayıtta DB varsayılanı |

### Örnek CSV

```csv
malzeme_kodu,malzeme_aciklamasi,uzunluk_m,sac_kalinlik,birim,birim_kilo,sac_fiyati,izole_fiyati,kilif_430_fiyati,fiyat_liste,aktif
CRN 100 001,Örnek baca parçası 100,,0.4,AD,2.5,100,120,90,450,1
CRN 100 002,İkinci kalem,3,0.5,M,5,200,220,180,899,evet
```

---

## 3. Kullanım

1. Admin → **Maliyet Parametreleri** veya **Ürünler**
2. **İçe Aktar (CSV/Excel)** → dosyayı seçin
3. Sütun eşlemesini kontrol edin → içe aktarın
4. Büyük dosyalar kuyrukta işlenebilir; `.env` içinde `QUEUE_CONNECTION=database` ve worker çalışıyor olmalıdır (mevcut sipariş/teklif import ile aynı mantık)

---

## 4. Örnek dosyalar (projede)

Aşağıdaki örnek şablonlar repoda bulunur:

- `storage/app/schemas/ornek_maliyet_parametreleri_import.csv`
- `storage/app/schemas/ornek_urunler_import.csv`

Bu dosyaları kopyalayıp Excel’de düzenleyebilir veya doğrudan içe aktarabilirsiniz.

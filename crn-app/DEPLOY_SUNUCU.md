# Sunucuya Deploy (Konsolsuz)

Bu proje yerelde **paketlenip** sunucuya FTP / dosya yöneticisi ile atılacak. Sunucuda terminal yok.

## 1. Yerelde yapılanlar (zaten yapıldı)

- `composer install --no-dev --optimize-autoloader` → **vendor** hazır
- `npm run build` → **public/build** hazır
- Laravel cache’ler temizlendi

## 2. Paketi oluşturma

Terminalde proje klasöründe:

```bash
chmod +x paketle-sunucu.sh
./paketle-sunucu.sh
```

Üst klasörde `crn-deploy-YYYYMMDD-HHMM.zip` oluşur. Bu dosyayı sunucuya atacaksın.

**Pakette olanlar:** Tüm uygulama kodu, `vendor/`, `public/build/`, `storage/` yapısı.  
**Pakette olmayanlar:** `.env`, `.git`, `node_modules`, log ve cache dosyaları.

## 3. Sunucuda yapılacaklar

### 3.1 Zip’i aç

Zip’i sunucuda web köküne (veya bir alt klasöre) yükle ve aç. Örnek yapı:

- `public_html/crn-app/` → veya
- `public_html/` (tüm site bu proje ise)

**Önemli:** Site adresi `https://site.com/admin` gibi olacaksa, Laravel’in **document root**’u `public` klasörü olmalı. Yani:

- Ya domain’in kökü = `.../crn-app/public`
- Ya da `public_html` içinde `index.php` ve `public` içeriği bir üstte olacak şekilde yapılandırılmış olmalı (shared hosting’te sık kullanılır).

### 3.2 .env dosyası

Pakette `.env` yok. Sunucuda **mutlaka** bir `.env` oluştur:

1. Yüklemiş olduğun projede `.env.example` var; onu kopyalayıp `.env` yap veya
2. Aşağıdaki örnekle sunucuda yeni `.env` oluştur.

**En az doldurulması gerekenler:**

```env
APP_NAME="CRN Baca"
APP_ENV=production
APP_KEY=base64:XXXXX
APP_DEBUG=false
APP_URL=https://SITE-ADRESINIZ.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=veritabani_adi
DB_USERNAME=veritabani_kullanici
DB_PASSWORD=veritabani_sifre

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

- **APP_KEY:** Yerelde `php artisan key:generate` ile üretip buraya yapıştırabilirsin; ya da sunucuda bir kez PHP çalıştırılabiliyorsa orada üretilir.
- **APP_URL:** Gerçek site adresi (https ile).
- **DB_***:** Hosting panelinden verilen MySQL bilgileri.
- **CACHE_STORE=file** ve **SESSION_DRIVER=file** → Sunucuda konsol olmadığı için database cache/session yerine dosya kullan (kurulumu kolay).

### 3.3 Klasör izinleri

Mümkünse şu klasörler yazılabilir (chmod 775 veya hosting panelinden “yazılabilir”):

- `storage`
- `storage/framework`
- `storage/logs`
- `bootstrap/cache`

Sunucuda sadece FTP / dosya yöneticisi varsa, genelde bu klasörler zaten yazılabilir olur; bir hata görürsen hosting desteğine “storage ve bootstrap/cache yazılabilir olmalı” diye yazabilirsin.

### 3.4 public/storage (isteğe bağlı)

Eğer uygulama içinde yüklenen dosyalar `storage`’da tutulup tarayıcıdan erişilecekse, `public/storage` → `../storage/app/public` sembolik bağlantısı gerekir. Konsol yoksa:

- Hosting panelinde “symlink” veya “sembolik link” destekleniyorsa aynı şeyi oradan kurabilirsin.
- Destek yoksa, yüklenen dosyalar için uygulama içinde doğrudan storage path kullanılıyorsa bazı hostings yine de çalıştırır; gerekirse sonra eklenir.

## 4. Kontrol

- Tarayıcıda `https://SITE-ADRESINIZ.com` (veya atığın klasöre göre `/admin`) aç.
- Admin: `/admin` → Giriş sayfası gelmeli.
- 500 hatası alırsan: `storage/logs/laravel.log` (sunucuda oluşur) içeriğine bak; çoğu zaman `.env` veya DB bilgisi / izin hatasıdır.

## 5. Özet checklist

| Adım | Yerel | Sunucu |
|------|--------|--------|
| 1 | `./paketle-sunucu.sh` ile zip al | Zip’i yükle ve aç |
| 2 | — | `.env` oluştur (APP_KEY, DB_*, APP_URL) |
| 3 | — | `storage` ve `bootstrap/cache` yazılabilir olsun |
| 4 | — | Domain’in document root’u `public` klasörüne işaret etsin |

PHP sürümü sunucuda **8.2 veya 8.3** olmalı (Laravel 11).

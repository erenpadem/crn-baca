# crn-baca

Laravel uygulaması `crn-app/` dizinindedir. Kurulum ve geliştirme adımları için [crn-app/README.md](crn-app/README.md) dosyasına bakın.

## Giriş bilgileri (geliştirme)

Bu hesaplar `php artisan db:seed` çalıştırıldıktan sonra veritabanında oluşur (`DatabaseSeeder`).

| Panel | Adres | E-posta | Şifre |
|--------|--------|---------|--------|
| **Admin (CRN)** | http://localhost:8000/admin | `admin@crn.local` | `password` |
| **Müşteri** | http://localhost:8000/musteri | `musteri@crn.local` | `password` |

Port veya host farklıysa (ör. Docker dışı `php artisan serve`), tablodaki `localhost:8000` kısmını kendi ortamınıza göre değiştirin.

### İsteğe bağlı test admin

Admin panele ek bir test kullanıcısı için (uygulama dizininde):

```bash
cd crn-app && php artisan crn:test-admin
```

Bu komut `test@crn.local` / `password123` kullanıcısını oluşturur veya günceller; giriş: http://localhost:8000/admin/login

---

**Not:** Bu şifreler yalnızca yerel/geliştirme içindir. Canlı ortamda güçlü parolalar kullanın ve örnek kullanıcıları üretimde bırakmayın.

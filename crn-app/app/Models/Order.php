<?php

namespace App\Models;

use App\Services\OrderCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @var array<string, float|bool|null>|null */
    protected ?array $pricingBreakdownCache = null;

    protected $guarded = [];

    public const DURUM_TASLAK = 'taslak';

    public const DURUM_MUSTERI_ONAYI_BEKLIYOR = 'musteri_onayi_bekliyor';

    public const DURUM_MUSTERI_ONAYLANDI = 'musteri_onaylandi';

    public const DURUM_YONETIM_ONAYI_BEKLIYOR = 'yonetim_onayi_bekliyor';

    public const DURUM_URETICI_ONAYI_BEKLIYOR = 'uretici_onayi_bekliyor';

    public const DURUM_ONAYLANDI = 'onaylandi';

    public const DURUM_BEKLEMEDE = 'beklemede';

    public const DURUM_IMALATTA = 'imalatta';

    public const DURUM_TAMAMLANDI = 'tamamlandi';

    /** Bayi, satış fiyatlarını gördükten sonra iskonto / birim fiyat karşı teklifi verdi. */
    public const DURUM_BAYI_KARSI_TEKLIF_VERDI = 'bayi_karsi_teklif_verdi';

    /**
     * Çizim / sipariş formu özellik kodları (alan adı => tam açıklama metni).
     *
     * @return array<string, string>
     */
    public static function ozellikKoduAciklamalari(): array
    {
        return [
            'attr_n' => 'N: Normal veya standart baca tipi (bazı firmalarda «non-insulated» anlamında da kullanılır).',
            'attr_m' => 'M: Metal (çoğunlukla çelik veya paslanmaz çelik).',
            'attr_a' => 'A: Alüminyum veya asbest içermeyen malzeme, bazı kataloglarda «adaptörlü» anlamı da olabilir.',
            'attr_h' => 'H: High temperature (yüksek sıcaklık dayanımı), baca sıcaklığına uygun sınıf.',
            'attr_di' => 'DI: Dış izoleli / Double Insulated veya galvanizli demir (firma standardına göre değişebilir).',
        ];
    }

    protected $casts = [
        'siparis_tarihi' => 'date',
        'iskonto_yuzde' => 'decimal:2',
        'bac_cap_mm' => 'decimal:2',
        'bac_yukseklik_mm' => 'decimal:2',
        'attr_n' => 'boolean',
        'attr_m' => 'boolean',
        'attr_a' => 'boolean',
        'attr_h' => 'boolean',
        'attr_di' => 'boolean',
        'kur' => 'decimal:4',
        'kur_farki_yuzde' => 'decimal:2',
        'tutar_kdvsiz_on' => 'decimal:4',
        'is_manual_on' => 'boolean',
        'tutar_kdvsiz_nihai' => 'decimal:4',
        'is_manual_nihai' => 'boolean',
        'opsiyonel_nakliye' => 'boolean',
        'nakliye_tutari' => 'decimal:4',
        'opsiyonel_akreditif' => 'boolean',
        'akreditif_tutari' => 'decimal:4',
        'opsiyonel_montaj' => 'boolean',
        'montaj_tutari' => 'decimal:4',
        'opsiyonel_havalandirma' => 'boolean',
        'havalandirma_tutari' => 'decimal:4',
        'opsiyonel_diger' => 'boolean',
        'diger_tutari' => 'decimal:4',
        'kdv_orani' => 'decimal:2',
        'kvkk_onay' => 'boolean',
        'bayiye_fiyat_goster' => 'boolean',
        'bayi_karsi_iskonto_yuzde' => 'decimal:2',
        'bayi_karsi_gonderim_at' => 'datetime',
        'yeni_seri_tarihi' => 'date',
        'imalat_listesi_cikti_at' => 'datetime',
    ];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            if (! $order->is_manual_on) {
                $order->tutar_kdvsiz_on = null;
            }
            if (! $order->is_manual_nihai) {
                $order->tutar_kdvsiz_nihai = null;
            }
        });
    }

    public function refresh(): self
    {
        $this->pricingBreakdownCache = null;

        return parent::refresh();
    }

    /** @return array<string, float|bool|null> */
    public function getPricingBreakdown(): array
    {
        return $this->pricingBreakdownCache ??= app(OrderCalculator::class)->calculate($this);
    }

    public function getToplamTutarAttribute(): float
    {
        return (float) $this->items->sum('tutar');
    }

    /** Kalem toplamı, iskonto sonrası (KDV hariç). */
    public function getKalemNetKdvsizAttribute(): float
    {
        return $this->getPricingBreakdown()['kalem_net_kdvsiz'];
    }

    /** Ön tutar: manuel (bayrak onaylı) veya kalemlerden. */
    public function getHesaplananKdvsizOnAttribute(): float
    {
        return $this->getPricingBreakdown()['hesaplanan_kdvsiz_on'];
    }

    /** Kur farkı sonrası nihai taban (KDV hariç). */
    public function getHesaplananKdvsizNihaiAttribute(): float
    {
        return $this->getPricingBreakdown()['hesaplanan_kdvsiz_nihai'];
    }

    public function getOpsiyonelToplamKdvsizAttribute(): float
    {
        return $this->getPricingBreakdown()['opsiyonel_toplam_kdvsiz'];
    }

    public function getAraToplamKdvsizAttribute(): float
    {
        return $this->getPricingBreakdown()['ara_toplam_kdvsiz'];
    }

    public function getKdvTutariAttribute(): float
    {
        return $this->getPricingBreakdown()['kdv_tutari'];
    }

    public function getGenelToplamAttribute(): float
    {
        return $this->getPricingBreakdown()['genel_toplam'];
    }

    /**
     * Baca çapı / yükseklik (mm) için gösterim: tam sayılarda ondalık yok (örn. 300), gerekirse en fazla bir ondalık.
     *
     * @param  string  $whenEmpty  Boş değerde dönecek metin (ör. infolist '–', CSV '').
     */
    public static function formatMmForDisplay(mixed $value, string $whenEmpty = '–'): string
    {
        if ($value === null || $value === '') {
            return $whenEmpty;
        }
        $f = (float) $value;
        if (abs($f - round($f)) < 0.0001) {
            return number_format((int) round($f), 0, ',', '.');
        }

        return number_format($f, 1, ',', '.');
    }

    public static function durumEtiketi(?string $durum): string
    {
        return match ($durum) {
            self::DURUM_TASLAK => 'Taslak',
            self::DURUM_MUSTERI_ONAYI_BEKLIYOR => 'Müşteri onayı bekleniyor',
            self::DURUM_MUSTERI_ONAYLANDI => 'Müşteri onayladı',
            self::DURUM_YONETIM_ONAYI_BEKLIYOR => 'Yönetim onayı bekleniyor',
            self::DURUM_URETICI_ONAYI_BEKLIYOR => 'Üretici onayı bekleniyor',
            self::DURUM_ONAYLANDI => 'Onaylandı',
            self::DURUM_BEKLEMEDE => 'Beklemede',
            self::DURUM_IMALATTA => 'İmalatta',
            self::DURUM_TAMAMLANDI => 'Tamamlandı',
            self::DURUM_BAYI_KARSI_TEKLIF_VERDI => 'Bayi karşı teklif verdi',
            default => filled($durum) ? (string) $durum : '—',
        };
    }

    public static function bayiKarsiTeklifEdebilir(self $order): bool
    {
        if (! $order->bayiye_fiyat_goster) {
            return false;
        }
        if ($order->durum === self::DURUM_TASLAK) {
            return false;
        }

        return ! in_array($order->durum, [
            self::DURUM_ONAYLANDI,
            self::DURUM_IMALATTA,
            self::DURUM_TAMAMLANDI,
        ], true);
    }

    /** Bayi paneli: henüz nihai onaylanmamış siparişler. */
    public static function bayiBekleyenDurumlari(): array
    {
        return [
            self::DURUM_TASLAK,
            self::DURUM_MUSTERI_ONAYI_BEKLIYOR,
            self::DURUM_MUSTERI_ONAYLANDI,
            self::DURUM_YONETIM_ONAYI_BEKLIYOR,
            self::DURUM_BAYI_KARSI_TEKLIF_VERDI,
            self::DURUM_URETICI_ONAYI_BEKLIYOR,
            self::DURUM_BEKLEMEDE,
        ];
    }

    public static function bayiOnayliDurumlari(): array
    {
        return [
            self::DURUM_ONAYLANDI,
            self::DURUM_IMALATTA,
            self::DURUM_TAMAMLANDI,
        ];
    }
}

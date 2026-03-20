<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
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
        'tutar_kdvsiz_nihai' => 'decimal:4',
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

    public function getToplamTutarAttribute(): float
    {
        return (float) $this->items->sum('tutar');
    }

    /** Kalem toplamı, iskonto sonrası (KDV hariç). */
    public function getKalemNetKdvsizAttribute(): float
    {
        $this->loadMissing('items');
        $toplam = (float) $this->items->sum('tutar');
        if ($this->iskonto_yuzde !== null && (float) $this->iskonto_yuzde > 0) {
            $toplam *= 1 - ((float) $this->iskonto_yuzde / 100);
        }

        return round($toplam, 4);
    }

    /** Ön tutar: manuel veya kalemlerden. */
    public function getHesaplananKdvsizOnAttribute(): float
    {
        if ($this->tutar_kdvsiz_on !== null) {
            return round((float) $this->tutar_kdvsiz_on, 4);
        }

        return round($this->kalem_net_kdvsiz, 4);
    }

    /** Kur farkı sonrası nihai taban (KDV hariç). */
    public function getHesaplananKdvsizNihaiAttribute(): float
    {
        if ($this->tutar_kdvsiz_nihai !== null) {
            return round((float) $this->tutar_kdvsiz_nihai, 4);
        }
        $on = $this->hesaplanan_kdvsiz_on;
        $yuzde = (float) ($this->kur_farki_yuzde ?? 0);

        return round($on * (1 + $yuzde / 100), 4);
    }

    public function getOpsiyonelToplamKdvsizAttribute(): float
    {
        $t = 0.0;
        if ($this->opsiyonel_nakliye && $this->nakliye_tutari) {
            $t += (float) $this->nakliye_tutari;
        }
        if ($this->opsiyonel_akreditif && $this->akreditif_tutari) {
            $t += (float) $this->akreditif_tutari;
        }
        if ($this->opsiyonel_montaj && $this->montaj_tutari) {
            $t += (float) $this->montaj_tutari;
        }
        if ($this->opsiyonel_havalandirma && $this->havalandirma_tutari) {
            $t += (float) $this->havalandirma_tutari;
        }
        if ($this->opsiyonel_diger && $this->diger_tutari) {
            $t += (float) $this->diger_tutari;
        }

        return round($t, 4);
    }

    public function getAraToplamKdvsizAttribute(): float
    {
        return round($this->hesaplanan_kdvsiz_nihai + $this->opsiyonel_toplam_kdvsiz, 4);
    }

    public function getKdvTutariAttribute(): float
    {
        $oran = (float) ($this->kdv_orani ?? 20);

        return round($this->ara_toplam_kdvsiz * ($oran / 100), 4);
    }

    public function getGenelToplamAttribute(): float
    {
        return round($this->ara_toplam_kdvsiz + $this->kdv_tutari, 4);
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

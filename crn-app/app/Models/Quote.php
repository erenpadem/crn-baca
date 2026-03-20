<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'teklif_no', 'dealer_id', 'created_by', 'durum',
        'proje_adi', 'cihaz_marka_model',
        'musteri_iskonto_yuzde', 'musteri_net_tutar', 'musteri_not', 'gonderim_tarihi', 'musteri_yanit_tarihi',
    ];

    protected $casts = [
        'gonderim_tarihi' => 'datetime',
        'musteri_yanit_tarihi' => 'datetime',
        'musteri_iskonto_yuzde' => 'decimal:2',
        'musteri_net_tutar' => 'decimal:4',
    ];

    public const DURUM_TASLAK = 'taslak';
    public const DURUM_GONDERILDI = 'gonderildi';
    public const DURUM_MUSTERI_TEKLIF_VERDI = 'musteri_teklif_verdi';
    public const DURUM_ONAYLANDI = 'onaylandi';
    public const DURUM_REDDEDILDI = 'reddedildi';

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }

    public function getToplamTutarAttribute(): float
    {
        return (float) $this->items->sum('tutar');
    }

    public function getMusteriToplamTutarAttribute(): ?float
    {
        if ($this->musteri_iskonto_yuzde === null) {
            return null;
        }
        $toplam = $this->toplam_tutar;
        return round($toplam * (1 - (float) $this->musteri_iskonto_yuzde / 100), 2);
    }
}

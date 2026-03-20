<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $guarded = [];

    protected $fillable = ['quote_id', 'product_id', 'birim_fiyat', 'adet', 'musteri_birim_fiyat', 'musteri_maliyet_birim', 'tutar'];

    protected $casts = [
        'birim_fiyat' => 'decimal:4',
        'adet' => 'decimal:4',
        'musteri_birim_fiyat' => 'decimal:4',
        'musteri_maliyet_birim' => 'decimal:4',
        'tutar' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (QuoteItem $item) {
            $adet = $item->adet !== null ? (float) $item->adet : null;
            if ($adet === null) {
                return;
            }
            $birim = $item->musteri_birim_fiyat ?? $item->musteri_maliyet_birim ?? $item->birim_fiyat;
            if ($birim !== null) {
                $item->tutar = round((float) $birim * $adet, 4);
            }
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

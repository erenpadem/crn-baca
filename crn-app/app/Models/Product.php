<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'malzeme_kodu', 'malzeme_aciklamasi', 'uzunluk_m', 'sac_kalinlik',
        'birim_kilo', 'birim', 'sac_fiyati', 'izole_fiyati', 'kilif_430_fiyati', 'fiyat_liste', 'aktif',
    ];

    protected $casts = [
        'uzunluk_m' => 'decimal:4',
        'sac_kalinlik' => 'decimal:4',
        'birim_kilo' => 'decimal:4',
        'sac_fiyati' => 'decimal:4',
        'izole_fiyati' => 'decimal:4',
        'kilif_430_fiyati' => 'decimal:4',
        'fiyat_liste' => 'decimal:4',
        'aktif' => 'boolean',
    ];

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }
}

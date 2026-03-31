<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = [];

    protected $fillable = ['order_id', 'product_id', 'birim_fiyat', 'adet', 'tutar', 'bayi_karsi_birim_fiyat'];

    protected $casts = [
        'birim_fiyat' => 'decimal:4',
        'adet' => 'decimal:4',
        'tutar' => 'decimal:4',
        'bayi_karsi_birim_fiyat' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saving(function (OrderItem $item) {
            if ($item->birim_fiyat !== null && $item->adet !== null) {
                $item->tutar = round($item->birim_fiyat * $item->adet, 4);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

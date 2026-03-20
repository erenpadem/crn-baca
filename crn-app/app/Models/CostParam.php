<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CostParam extends Model
{
    protected $guarded = [];

    protected $fillable = ['name', 'key', 'value', 'unit'];

    protected $casts = [
        'value' => 'decimal:4',
    ];

    public static function getByKey(string $key): ?float
    {
        $param = Cache::remember('cost_param_' . $key, 3600, fn () => self::where('key', $key)->first());
        return $param ? (float) $param->value : null;
    }

    /** Excel genel hesap: Ağırlık (kg) = çap * pi * kalınlık * yoğunluk / 100 */
    public static function hesaplaAgirlik(float $cap, float $kalinlik = 0.4, float $yogunluk = 0.8): float
    {
        $pi = 3.14;
        return round($cap * $pi * $kalinlik * $yogunluk / 100, 4);
    }
}

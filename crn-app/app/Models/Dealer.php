<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dealer extends Model
{
    protected $fillable = [
        'firma_no', 'unvan', 'adres', 'il_ilce', 'ilgili_kisi',
        'tel', 'tel_2', 'mail', 'sevk_adresi',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'dealer_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'dealer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'dealer_id');
    }
}

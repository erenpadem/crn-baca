<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personel extends Model
{
    protected $table = 'personel';

    protected $fillable = [
        'ad_soyad', 'departman', 'pozisyon', 'telefon', 'email',
        'evli', 'dogum_yeri', 'acil_durum_kisi', 'acil_durum_telefonu',
        'kan_grubu', 'aktif',
    ];

    protected $casts = [
        'evli' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function puantajlar(): HasMany
    {
        return $this->hasMany(Puantaj::class, 'personel_id');
    }
}

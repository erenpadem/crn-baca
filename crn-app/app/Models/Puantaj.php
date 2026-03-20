<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puantaj extends Model
{
    protected $table = 'puantaj';

    protected $fillable = [
        'personel_id', 'tarih', 'durum', 'aciklama', 'notlar',
        'giris_saati', 'cikis_saati',
    ];

    protected $casts = [
        'tarih' => 'date',
    ];

    public const DURUM_TAM_GUN = 'tam_gun';
    public const DURUM_YARIM_GUN = 'yarim_gun';
    public const DURUM_GELMEDI = 'gelmedi';

    public static function durumlar(): array
    {
        return [
            self::DURUM_TAM_GUN => 'Tam Gün',
            self::DURUM_YARIM_GUN => 'Yarım Gün',
            self::DURUM_GELMEDI => 'Gelmedi',
        ];
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }
}

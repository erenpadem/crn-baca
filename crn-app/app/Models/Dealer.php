<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dealer extends Model
{
    protected $fillable = [
        'panel_user_id',
        'firma_no', 'unvan', 'adres', 'il_ilce', 'ilgili_kisi',
        'tel', 'tel_2', 'mail', 'sevk_adresi',
    ];

    public function panelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'panel_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'dealer_id');
    }

    /**
     * Firma paneli (/musteri) için seçilen kullanıcıya firma, rol ve isteğe bağlı şifre uygular.
     */
    public function syncPanelUser(?string $plainPassword): void
    {
        if ($this->panel_user_id === null) {
            return;
        }

        $user = User::query()->find($this->panel_user_id);
        if ($user === null) {
            return;
        }

        if ($user->hasRole('admin') || $user->hasRole('satis')) {
            return;
        }

        $user->dealer_id = $this->id;

        if ($plainPassword !== null && $plainPassword !== '') {
            $user->password = $plainPassword;
        }

        $user->save();

        $bayiRole = Role::query()->where('name', 'bayi')->first();
        if ($bayiRole !== null && ! $user->hasRole('bayi')) {
            $user->roles()->attach($bayiRole);
        }
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

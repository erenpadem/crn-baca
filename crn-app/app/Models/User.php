<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'dealer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function hasRole(string $name): bool
    {
        return $this->roles()->where('name', $name)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole('admin') || $this->hasRole('satis') || $this->hasRole('imalathane');
        }
        if ($panel->getId() === 'musteri') {
            return $this->hasRole('musteri')
                && ! $this->hasRole('admin')
                && ! $this->hasRole('satis')
                && $this->dealer_id !== null;
        }
        if ($panel->getId() === 'bayi') {
            return $this->hasRole('bayi')
                && ! $this->hasRole('admin')
                && ! $this->hasRole('satis')
                && $this->dealer_id !== null;
        }

        return false;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        if (! in_array($panel->getId(), ['musteri', 'bayi'], true) || $this->dealer_id === null) {
            return collect();
        }
        $dealer = $this->dealer;

        return $dealer ? collect([$dealer]) : collect();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->dealer_id !== null
            && $tenant->getKey() === $this->dealer_id;
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        if (! in_array($panel->getId(), ['musteri', 'bayi'], true)) {
            return null;
        }

        return $this->dealer;
    }
}

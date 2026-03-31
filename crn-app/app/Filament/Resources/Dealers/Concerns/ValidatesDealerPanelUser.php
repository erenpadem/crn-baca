<?php

namespace App\Filament\Resources\Dealers\Concerns;

use App\Models\Dealer;
use App\Models\User;
use Illuminate\Validation\ValidationException;

trait ValidatesDealerPanelUser
{
    protected function validateDealerPanelUserSelection(?int $userId, ?int $excludeDealerId = null): void
    {
        if ($userId === null) {
            return;
        }

        $user = User::query()->find($userId);
        if ($user === null) {
            return;
        }

        if ($user->hasRole('admin') || $user->hasRole('satis')) {
            throw ValidationException::withMessages([
                'panel_user_id' => 'Yönetici veya satış kullanıcısı firma paneli için seçilemez.',
            ]);
        }

        $query = Dealer::query()->where('panel_user_id', $userId);
        if ($excludeDealerId !== null) {
            $query->where('id', '!=', $excludeDealerId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages([
                'panel_user_id' => 'Bu kullanıcı zaten başka bir bayinin panel hesabı olarak atanmış.',
            ]);
        }
    }
}

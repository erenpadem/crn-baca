<?php

namespace App\Filament\Resources\Dealers\Pages;

use App\Filament\Resources\Dealers\Concerns\ValidatesDealerPanelUser;
use App\Filament\Resources\Dealers\DealerResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateDealer extends CreateRecord
{
    use ValidatesDealerPanelUser;

    protected static string $resource = DealerResource::class;

    protected ?string $pendingPanelPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingPanelPassword = $data['panel_password'] ?? null;
        unset($data['panel_password']);

        if (! empty($data['panel_user_id'])) {
            $this->validateDealerPanelUserSelection((int) $data['panel_user_id']);
            $user = User::query()->find($data['panel_user_id']);
            $data['mail'] = $user?->email;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPanelUser($this->pendingPanelPassword);
    }
}

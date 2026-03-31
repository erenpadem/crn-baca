<?php

namespace App\Filament\Resources\Dealers\Pages;

use App\Filament\Resources\Dealers\Concerns\ValidatesDealerPanelUser;
use App\Filament\Resources\Dealers\DealerResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDealer extends EditRecord
{
    use ValidatesDealerPanelUser;

    protected static string $resource = DealerResource::class;

    protected ?string $pendingPanelPassword = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingPanelPassword = $data['panel_password'] ?? null;
        unset($data['panel_password']);

        if (! empty($data['panel_user_id'])) {
            $this->validateDealerPanelUserSelection((int) $data['panel_user_id'], (int) $this->record->getKey());
            $user = User::query()->find($data['panel_user_id']);
            $data['mail'] = $user?->email;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPanelUser($this->pendingPanelPassword);
    }
}

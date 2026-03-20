<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Kullanıcı düzenle';

    /** Rol ID'leri mutateFormDataBeforeSave'da alınıp afterSave'da sync için kullanılır. */
    protected array $pendingRoleIds = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingRoleIds = is_array($data['roles'] ?? null) ? $data['roles'] : [];
        unset($data['roles']);
        return $data;
    }

    protected function afterSave(): void
    {
        $ids = array_values(array_filter(array_map('intval', (array) $this->pendingRoleIds)));
        $this->getRecord()->roles()->sync($ids);
    }
}

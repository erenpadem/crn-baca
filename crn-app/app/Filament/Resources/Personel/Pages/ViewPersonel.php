<?php

namespace App\Filament\Resources\Personel\Pages;

use App\Filament\Resources\Personel\PersonelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPersonel extends ViewRecord
{
    protected static string $resource = PersonelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

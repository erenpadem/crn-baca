<?php

namespace App\Filament\Resources\Puantaj\Pages;

use App\Filament\Resources\Puantaj\PuantajResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPuantaj extends ViewRecord
{
    protected static string $resource = PuantajResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

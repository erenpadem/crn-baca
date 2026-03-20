<?php

namespace App\Filament\Resources\Puantaj\Pages;

use App\Filament\Resources\Puantaj\PuantajResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPuantaj extends EditRecord
{
    protected static string $resource = PuantajResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

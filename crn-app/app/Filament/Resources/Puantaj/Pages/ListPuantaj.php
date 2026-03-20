<?php

namespace App\Filament\Resources\Puantaj\Pages;

use App\Filament\Resources\Puantaj\PuantajResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPuantaj extends ListRecords
{
    protected static string $resource = PuantajResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

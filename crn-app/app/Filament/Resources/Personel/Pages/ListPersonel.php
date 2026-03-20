<?php

namespace App\Filament\Resources\Personel\Pages;

use App\Filament\Resources\Personel\PersonelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPersonel extends ListRecords
{
    protected static string $resource = PersonelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CostParams\Pages;

use App\Filament\Imports\CostParamImporter;
use App\Filament\Resources\CostParams\CostParamResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListCostParams extends ListRecords
{
    protected static string $resource = CostParamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(CostParamImporter::class)
                ->label('İçe Aktar (CSV/Excel)'),
            CreateAction::make(),
        ];
    }
}

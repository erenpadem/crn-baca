<?php

namespace App\Filament\Resources\CostParams\Pages;

use App\Filament\Resources\CostParams\CostParamResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCostParam extends ViewRecord
{
    protected static string $resource = CostParamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

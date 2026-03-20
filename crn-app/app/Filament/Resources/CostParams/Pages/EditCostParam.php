<?php

namespace App\Filament\Resources\CostParams\Pages;

use App\Filament\Resources\CostParams\CostParamResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCostParam extends EditRecord
{
    protected static string $resource = CostParamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

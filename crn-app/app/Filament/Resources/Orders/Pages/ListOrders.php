<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Exports\OrderExporter;
use App\Filament\Imports\OrderItemImporter;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(OrderItemImporter::class)
                ->label('Kalem İçe Aktar (CSV/Excel)'),
            ExportAction::make()
                ->exporter(OrderExporter::class)
                ->label('Liste Dışa Aktar'),
            CreateAction::make(),
        ];
    }
}

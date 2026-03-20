<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Filament\Exports\QuoteExporter;
use App\Filament\Imports\QuoteItemImporter;
use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(QuoteItemImporter::class)
                ->label('Kalem İçe Aktar (CSV/Excel)'),
            ExportAction::make()
                ->exporter(QuoteExporter::class)
                ->label('Liste Dışa Aktar'),
            CreateAction::make(),
        ];
    }
}

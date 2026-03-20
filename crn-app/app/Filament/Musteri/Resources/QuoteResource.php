<?php

namespace App\Filament\Musteri\Resources;

use App\Filament\Musteri\Resources\QuoteResource\Pages\ListQuotes;
use App\Filament\Musteri\Resources\QuoteResource\Pages\ViewQuote;
use App\Models\Quote;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationLabel = 'Tekliflerim';

    protected static string|\UnitEnum|null $navigationGroup = 'Tekliflerim';

    protected static ?string $modelLabel = 'Teklif';

    protected static ?string $pluralModelLabel = 'Teklifler';

    protected static ?string $recordTitleAttribute = 'teklif_no';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->where('durum', '!=', 'taslak');
                if (Filament::getTenant()) {
                    $query->where('dealer_id', Filament::getTenant()->getKey());
                }
                return $query;
            })
            ->summaries(false, false)
            ->columns([
                TextColumn::make('teklif_no')->label('Teklif No')->searchable()->sortable(),
                TextColumn::make('durum')->label('Durum')->badge()->formatStateUsing(fn ($state) => match ((string) $state) {
                    'taslak' => 'Taslak',
                    'gonderildi' => 'Gönderildi',
                    'musteri_teklif_verdi' => 'Müşteri Teklif Verdi',
                    'onaylandi' => 'Onaylandı',
                    'reddedildi' => 'Reddedildi',
                    default => filled($state) ? (string) $state : 'Taslak',
                }),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y')->sortable(),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotes::route('/'),
            'view' => ViewQuote::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

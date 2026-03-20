<?php

namespace App\Filament\Musteri\Resources;

use App\Filament\Musteri\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Musteri\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Models\Order;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationLabel = 'Siparişler';

    protected static string|\UnitEnum|null $navigationGroup = 'Siparişler';

    protected static ?string $modelLabel = 'Sipariş';

    protected static ?string $pluralModelLabel = 'Siparişler';

    protected static ?string $recordTitleAttribute = 'siparis_no';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                if (Filament::getTenant()) {
                    $query->where('dealer_id', Filament::getTenant()->getKey());
                }

                return $query;
            })
            ->columns([
                TextColumn::make('siparis_no')->label('Sipariş No')->searchable()->sortable(),
                TextColumn::make('durum')->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Order::durumEtiketi($state)),
                TextColumn::make('siparis_tarihi')->label('Tarih')->date('d.m.Y')->sortable(),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

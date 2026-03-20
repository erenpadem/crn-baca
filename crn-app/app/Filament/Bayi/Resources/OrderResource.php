<?php

namespace App\Filament\Bayi\Resources;

use App\Filament\Bayi\Resources\OrderResource\Pages;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Siparişlerim';

    protected static ?string $navigationLabel = 'Teklif taleplerim';

    protected static ?string $modelLabel = 'Teklif talebi';

    protected static ?string $pluralModelLabel = 'Teklif talepleri';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema, OrderForm::CONTEXT_BAYI);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema, OrderInfolist::CONTEXT_BAYI);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siparis_no')->label('Talep no')->searchable()->sortable(),
                TextColumn::make('on_siparis_no')->label('Ön sip.')->placeholder('—'),
                TextColumn::make('siparis_tarihi')->label('Tarih')->date('d.m.Y')->sortable(),
                TextColumn::make('durum')->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Order::durumEtiketi($state)),
                TextColumn::make('items_count')->label('Kalem')->counts('items'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Order $record) => $record->durum === Order::DURUM_TASLAK),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

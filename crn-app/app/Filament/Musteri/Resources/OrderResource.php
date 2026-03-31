<?php

namespace App\Filament\Musteri\Resources;

use App\Filament\Musteri\Resources\OrderResource\Pages;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Models\Order;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationLabel = 'Siparişler';

    protected static string|\UnitEnum|null $navigationGroup = 'Siparişler';

    protected static ?string $modelLabel = 'Sipariş';

    protected static ?string $pluralModelLabel = 'Siparişler';

    protected static ?string $recordTitleAttribute = 'siparis_no';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 0;

    protected static function authUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public static function form(Schema $schema): Schema
    {
        $user = self::authUser();
        $ctx = ($user && $user->hasRole('bayi')) ? OrderForm::CONTEXT_BAYI : OrderForm::CONTEXT_ADMIN;

        return OrderForm::configure($schema, $ctx);
    }

    public static function infolist(Schema $schema): Schema
    {
        $user = self::authUser();
        $ctx = ($user && $user->hasRole('bayi')) ? OrderInfolist::CONTEXT_BAYI : OrderInfolist::CONTEXT_ADMIN;

        return OrderInfolist::configure($schema, $ctx);
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
                TextColumn::make('siparis_no')->label('Talep / sipariş no')->searchable()->sortable(),
                TextColumn::make('on_siparis_no')->label('Ön sip.')->placeholder('—'),
                TextColumn::make('durum')->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Order::durumEtiketi($state)),
                TextColumn::make('siparis_tarihi')->label('Tarih')->date('d.m.Y')->sortable(),
                TextColumn::make('items_count')->label('Kalem')->counts('items'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(function (Order $record) {
                        if (! self::authUser()?->hasRole('bayi')) {
                            return false;
                        }

                        return $record->durum === Order::DURUM_TASLAK;
                    }),
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

    public static function canCreate(): bool
    {
        return self::authUser()?->hasRole('bayi') ?? false;
    }
}

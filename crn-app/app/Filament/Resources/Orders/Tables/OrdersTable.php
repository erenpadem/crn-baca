<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siparis_no')->label('Sipariş No')->searchable()->sortable(),
                TextColumn::make('on_siparis_no')->label('Ön sip.')->searchable()->toggleable(),
                TextColumn::make('dealer.unvan')->label('Müşteri')->searchable()->sortable(),
                TextColumn::make('siparis_tarihi')->label('Tarih')->date('d.m.Y')->sortable(),
                TextColumn::make('durum')->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Order::durumEtiketi($state))
                    ->color(fn ($state) => match ((string) $state) {
                        Order::DURUM_TASLAK => 'gray',
                        Order::DURUM_MUSTERI_ONAYI_BEKLIYOR => 'warning',
                        Order::DURUM_MUSTERI_ONAYLANDI => 'info',
                        Order::DURUM_YONETIM_ONAYI_BEKLIYOR => 'warning',
                        Order::DURUM_URETICI_ONAYI_BEKLIYOR => 'danger',
                        Order::DURUM_ONAYLANDI => 'success',
                        Order::DURUM_BEKLEMEDE => 'gray',
                        Order::DURUM_IMALATTA => 'primary',
                        Order::DURUM_TAMAMLANDI => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('items_count')->label('Kalem')->counts('items'),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Quotes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teklif_no')->label('Teklif No')->searchable()->sortable(),
                TextColumn::make('dealer.unvan')->label('Müşteri')->searchable()->sortable(),
                TextColumn::make('durum')->label('Durum')->badge()->formatStateUsing(function ($state, $record = null) {
                    $val = $record?->durum ?? $state ?? '';
                    return match ((string) $val) {
                        'taslak' => 'Taslak',
                        'gonderildi' => 'Gönderildi',
                        'musteri_teklif_verdi' => 'Müşteri Teklif Verdi',
                        'onaylandi' => 'Onaylandı',
                        'reddedildi' => 'Reddedildi',
                        default => filled($val) ? (string) $val : 'Taslak',
                    };
                }),
                TextColumn::make('items_count')->label('Kalem')->counts('items'),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y')->sortable(),
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

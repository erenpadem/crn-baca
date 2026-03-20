<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('malzeme_kodu')->label('Kod')->searchable()->sortable(),
                TextColumn::make('malzeme_aciklamasi')->label('Açıklama')->searchable()->limit(40),
                TextColumn::make('birim')->label('Birim'),
                TextColumn::make('fiyat_liste')->label('Fiyat Liste')->numeric(decimalPlaces: 2)->sortable(),
                IconColumn::make('aktif')->label('Aktif')->boolean(),
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

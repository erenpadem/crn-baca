<?php

namespace App\Filament\Resources\Dealers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DealersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('firma_no')->label('Firma No')->searchable()->sortable(),
                TextColumn::make('unvan')->label('Ünvan')->searchable()->sortable(),
                TextColumn::make('il_ilce')->label('İl/İlçe')->searchable(),
                TextColumn::make('ilgili_kisi')->label('İlgili')->searchable(),
                TextColumn::make('tel')->label('Tel'),
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

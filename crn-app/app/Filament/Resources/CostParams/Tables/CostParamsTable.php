<?php

namespace App\Filament\Resources\CostParams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostParamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Görünen ad')->searchable()->sortable(),
                TextColumn::make('key')->label('Parametre kodu')->searchable(),
                TextColumn::make('value')->label('Değer')->numeric(decimalPlaces: 4)->sortable(),
                TextColumn::make('unit')->label('Birim'),
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

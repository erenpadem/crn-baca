<?php

namespace App\Filament\Resources\Personel\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PersonelTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ad_soyad')->label('Ad Soyad')->searchable()->sortable(),
                TextColumn::make('departman')->label('Departman')->searchable()->sortable()->badge(),
                TextColumn::make('pozisyon')->label('Pozisyon')->searchable(),
                TextColumn::make('telefon')->label('Telefon'),
                TextColumn::make('email')->label('E-posta'),
                IconColumn::make('evli')->label('Evli')->boolean(),
                TextColumn::make('acil_durum_kisi')->label('Acil Durum Kişisi')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('acil_durum_telefonu')->label('Acil Durum Tel')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kan_grubu')->label('Kan Grubu')->badge()->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('aktif')->label('Aktif')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('aktif')->label('Aktif')->placeholder('Tümü')->trueLabel('Aktif')->falseLabel('Pasif'),
                SelectFilter::make('departman')
                    ->label('Departman')
                    ->options([
                        'Muhasebe' => 'Muhasebe',
                        'Mühendislik' => 'Mühendislik',
                        'Montaj' => 'Montaj',
                        'İmalathane' => 'İmalathane',
                        'Satış' => 'Satış',
                        'Genel' => 'Genel',
                    ]),
            ])
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

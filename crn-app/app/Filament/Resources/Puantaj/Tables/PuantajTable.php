<?php

namespace App\Filament\Resources\Puantaj\Tables;

use App\Models\Puantaj;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PuantajTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('personel.ad_soyad')->label('Personel')->searchable()->sortable(),
                TextColumn::make('tarih')->label('Tarih')->date('d.m.Y')->sortable(),
                TextColumn::make('durum')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Puantaj::durumlar()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Puantaj::DURUM_TAM_GUN => 'success',
                        Puantaj::DURUM_YARIM_GUN => 'warning',
                        Puantaj::DURUM_GELMEDI => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('aciklama')->label('Açıklama')->limit(30)->placeholder('–')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('giris_saati')->label('Giriş')->formatStateUsing(fn ($state) => filled($state) ? \Carbon\Carbon::parse($state)->format('H:i') : '–')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cikis_saati')->label('Çıkış')->formatStateUsing(fn ($state) => filled($state) ? \Carbon\Carbon::parse($state)->format('H:i') : '–')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('durum')
                    ->label('Durum')
                    ->options(Puantaj::durumlar())
                    ->placeholder('Tüm Kayıtlar'),
                SelectFilter::make('personel_id')
                    ->label('Personel')
                    ->relationship('personel', 'ad_soyad')
                    ->searchable()
                    ->preload(),
                Filter::make('tarih')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('tarih_from')->label('Başlangıç'),
                        \Filament\Forms\Components\DatePicker::make('tarih_to')->label('Bitiş'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['tarih_from'] ?? null) {
                            $query->whereDate('tarih', '>=', $data['tarih_from']);
                        }
                        if ($data['tarih_to'] ?? null) {
                            $query->whereDate('tarih', '<=', $data['tarih_to']);
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tarih', 'desc');
    }
}

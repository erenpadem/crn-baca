<?php

namespace App\Filament\Resources\Personel\RelationManagers;

use App\Models\Puantaj;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PuantajRelationManager extends RelationManager
{
    protected static string $relationship = 'puantajlar';

    protected static ?string $title = 'Puantaj Geçmişi';

    protected static ?string $modelLabel = 'Puantaj';

    protected static ?string $pluralModelLabel = 'Puantaj';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tarih')
            ->columns([
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
                TextColumn::make('aciklama')->label('Açıklama')->limit(40)->placeholder('–'),
                TextColumn::make('giris_saati')->label('Giriş')->formatStateUsing(fn ($state) => filled($state) ? \Carbon\Carbon::parse($state)->format('H:i') : '–'),
                TextColumn::make('cikis_saati')->label('Çıkış')->formatStateUsing(fn ($state) => filled($state) ? \Carbon\Carbon::parse($state)->format('H:i') : '–'),
            ])
            ->defaultSort('tarih', 'desc')
            ->filters([])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->form([
                        DatePicker::make('tarih')->label('Tarih')->required()->default(now())->native(false),
                        Select::make('durum')
                            ->label('Durum')
                            ->options(Puantaj::durumlar())
                            ->required()
                            ->default(Puantaj::DURUM_TAM_GUN)
                            ->native(false),
                        Textarea::make('aciklama')->label('Açıklama (Neden gelmedi?)')->rows(2),
                        Textarea::make('notlar')->label('Notlar')->rows(2),
                        TimePicker::make('giris_saati')->label('Giriş Saati')->seconds(false),
                        TimePicker::make('cikis_saati')->label('Çıkış Saati')->seconds(false),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['personel_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->form([
                        DatePicker::make('tarih')->label('Tarih')->required()->native(false),
                        Select::make('durum')->label('Durum')->options(Puantaj::durumlar())->required()->native(false),
                        Textarea::make('aciklama')->label('Açıklama')->rows(2),
                        Textarea::make('notlar')->label('Notlar')->rows(2),
                        TimePicker::make('giris_saati')->label('Giriş Saati')->seconds(false),
                        TimePicker::make('cikis_saati')->label('Çıkış Saati')->seconds(false),
                    ]),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }
}

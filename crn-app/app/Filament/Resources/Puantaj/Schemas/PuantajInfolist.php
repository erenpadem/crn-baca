<?php

namespace App\Filament\Resources\Puantaj\Schemas;

use App\Models\Puantaj;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PuantajInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Puantaj Bilgileri')
                    ->schema([
                        TextEntry::make('personel.ad_soyad')->label('Personel'),
                        TextEntry::make('tarih')->label('Tarih')->date('d.m.Y'),
                        TextEntry::make('durum')
                            ->label('Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state) => Puantaj::durumlar()[$state] ?? $state),
                        TextEntry::make('aciklama')->label('Açıklama (Neden gelmedi)')->placeholder('–'),
                        TextEntry::make('notlar')->label('Notlar')->placeholder('–'),
                        TextEntry::make('giris_saati')->label('Giriş Saati')->formatStateUsing(fn ($s) => $s ? \Carbon\Carbon::parse($s)->format('H:i') : '–'),
                        TextEntry::make('cikis_saati')->label('Çıkış Saati')->formatStateUsing(fn ($s) => $s ? \Carbon\Carbon::parse($s)->format('H:i') : '–'),
                    ])->columns(2),
            ]);
    }
}

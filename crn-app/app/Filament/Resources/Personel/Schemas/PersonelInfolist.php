<?php

namespace App\Filament\Resources\Personel\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PersonelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 1,
            ])
            ->components([
                Section::make('Kişisel Bilgiler')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('ad_soyad')->label('Ad Soyad'),
                        TextEntry::make('departman')->label('Departman'),
                        TextEntry::make('pozisyon')->label('Pozisyon'),
                        TextEntry::make('telefon')->label('Telefon'),
                        TextEntry::make('email')->label('E-posta'),
                        TextEntry::make('evli')->label('Evli')->badge()->formatStateUsing(fn ($state) => $state ? 'Evet' : 'Hayır'),
                        TextEntry::make('dogum_yeri')->label('Doğum Yeri'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Acil Durum Bilgileri')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('acil_durum_kisi')->label('Acil Durum Kişisi'),
                        TextEntry::make('acil_durum_telefonu')->label('Acil Durum Telefonu'),
                        TextEntry::make('kan_grubu')->label('Kan Grubu'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Durum')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('aktif')->label('Aktif')->badge()->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif'),
                    ]),
            ]);
    }
}

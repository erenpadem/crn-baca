<?php

namespace App\Filament\Resources\Personel\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PersonelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kişisel Bilgiler')
                    ->description('Personel iletişim ve temel bilgiler.')
                    ->schema([
                        TextInput::make('ad_soyad')->label('Ad Soyad')->required()->maxLength(255),
                        Select::make('departman')
                            ->label('Departman')
                            ->options(self::departmanSecenekleri())
                            ->searchable()
                            ->nullable(),
                        TextInput::make('pozisyon')->label('Pozisyon')->maxLength(100),
                        TextInput::make('telefon')->label('Telefon')->tel()->maxLength(50),
                        TextInput::make('email')->label('E-posta')->email()->maxLength(255),
                        Toggle::make('evli')->label('Evli')->default(false),
                        TextInput::make('dogum_yeri')->label('Doğum Yeri')->maxLength(255),
                    ])->columns(2),
                Section::make('Acil Durum Bilgileri')
                    ->schema([
                        TextInput::make('acil_durum_kisi')->label('Acil Durum Kişisi')->maxLength(255),
                        TextInput::make('acil_durum_telefonu')->label('Acil Durum Telefonu')->tel()->maxLength(50),
                        Select::make('kan_grubu')
                            ->label('Kan Grubu')
                            ->options([
                                'A+' => 'A+', 'A-' => 'A-',
                                'B+' => 'B+', 'B-' => 'B-',
                                'AB+' => 'AB+', 'AB-' => 'AB-',
                                '0+' => '0+', '0-' => '0-',
                            ])
                            ->nullable(),
                    ])->columns(2),
                Section::make('Durum')
                    ->schema([
                        Toggle::make('aktif')->label('Aktif Personel')->default(true),
                    ]),
            ]);
    }

    protected static function departmanSecenekleri(): array
    {
        return [
            'Muhasebe' => 'Muhasebe',
            'Mühendislik' => 'Mühendislik',
            'Montaj' => 'Montaj',
            'İmalathane' => 'İmalathane',
            'Satış' => 'Satış',
            'Genel' => 'Genel',
        ];
    }
}

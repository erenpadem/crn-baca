<?php

namespace App\Filament\Resources\Dealers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DealerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bayi / Müşteri Bilgileri')
                    ->description('Excel’deki BAYİ sayfası ve Sipariş Formu başlığındaki firma bilgileri.')
                    ->schema([
                        TextInput::make('firma_no')->label('Firma No')->maxLength(50)->placeholder('Örn: F-1')->columnSpanFull(),
                        TextInput::make('unvan')->label('Ünvan')->required()->maxLength(255)->columnSpanFull(),
                        Textarea::make('adres')->label('Adres')->rows(2)->columnSpanFull(),
                        TextInput::make('il_ilce')->label('İl / İlçe')->maxLength(255),
                        TextInput::make('ilgili_kisi')->label('İlgili Kişi')->maxLength(255),
                        TextInput::make('tel')->label('Tel')->tel()->maxLength(50),
                        TextInput::make('tel_2')->label('Tel-2')->tel()->maxLength(50),
                        TextInput::make('mail')->label('Mail')->email()->maxLength(255)->columnSpanFull(),
                        Textarea::make('sevk_adresi')->label('Sevk Adresi')->rows(2)->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}

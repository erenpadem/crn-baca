<?php

namespace App\Filament\Resources\Dealers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DealerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 1,
            ])
            ->components([
                Section::make('Bayi / Müşteri Bilgileri')
                    ->description('Excel’deki BAYİ sayfası ve Sipariş Formu başlığındaki firma bilgileri.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('firma_no')->label('Firma No')->maxLength(50)->placeholder('Örn: F-1')->columnSpanFull(),
                        TextInput::make('unvan')->label('Ünvan')->required()->maxLength(255)->columnSpanFull(),
                        Textarea::make('adres')->label('Adres')->rows(2)->columnSpanFull(),
                        TextInput::make('il_ilce')->label('İl / İlçe')->maxLength(255),
                        TextInput::make('ilgili_kisi')->label('İlgili Kişi')->maxLength(255),
                        TextInput::make('tel')->label('Tel')->tel()->maxLength(50),
                        TextInput::make('tel_2')->label('Tel-2')->tel()->maxLength(50),
                        Textarea::make('sevk_adresi')->label('Sevk Adresi')->rows(2)->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Firma paneli girişi')
                    ->description('E-posta, sistemdeki kullanıcı kaydından gelir. Önce Kullanıcılar’dan hesap oluşturun; burada seçin. /musteri adresinde bu e-posta ve şifre ile giriş yapılır.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('panel_user_id')
                            ->label('Panel kullanıcısı')
                            ->relationship('panelUser', 'email', fn ($query) => $query->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        TextInput::make('panel_password')
                            ->label('Panel şifresi')
                            ->password()
                            ->maxLength(255)
                            ->helperText('Seçilen kullanıcının giriş şifresi. Düzenlemede boş bırakırsanız şifre değişmez; ilk kez atıyorsanız veya sıfırlamak için doldurun.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

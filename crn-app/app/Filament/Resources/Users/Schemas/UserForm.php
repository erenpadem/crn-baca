<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kullanıcı Bilgileri')
                    ->description('Admin: yönetim paneli. Müşteri: teklif onayı. Bayi: sipariş oluşturma (/bayi). Bu rollerde bayi (firma) seçilmelidir.')
                    ->schema([
                        TextInput::make('name')->label('Ad')->required()->maxLength(255),
                        TextInput::make('email')->label('E-posta')->email()->required()->maxLength(255),
                        Select::make('dealer_id')
                            ->label('Bayi')
                            ->relationship('dealer', 'unvan')
                            ->searchable()
                            ->preload()
                            ->noOptionsMessage('Bayi bulunamadı. Müşteri rolü için önce Bayiler sayfasından bayi ekleyin.'),
                        TextInput::make('password')
                            ->label('Şifre')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->helperText('Düzenlemede boş bırakırsanız şifre değişmez.'),
                    ])->columns(2),
                Section::make('Roller')
                    ->description('Admin / Satış / İmalathane: /admin. Müşteri: /musteri. Bayi: /bayi. Müşteri ve Bayi için firma zorunludur.')
                    ->schema([
                        CheckboxList::make('roles')
                            ->relationship('roles', 'label')
                            ->label('Roller')
                            ->columns(2),
                    ]),
            ]);
    }
}

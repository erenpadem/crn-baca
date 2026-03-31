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
            ->columns([
                'default' => 1,
                'lg' => 1,
            ])
            ->components([
                Section::make('Kullanıcı Bilgileri')
                    ->description('Admin: yönetim paneli. Müşteri ve bayi: firma portalı (/musteri) — teklif onayı ve sipariş talebi. Bu rollerde firma (bayi) seçilmelidir.')
                    ->columnSpanFull()
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
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Roller')
                    ->description('Admin / Satış / İmalathane: /admin. Müşteri ve bayi: /musteri (tek firma portalı). Bu rollerde firma zorunludur.')
                    ->columnSpanFull()
                    ->schema([
                        CheckboxList::make('roles')
                            ->relationship('roles', 'label')
                            ->label('Roller')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ]),
                    ]),
            ]);
    }
}

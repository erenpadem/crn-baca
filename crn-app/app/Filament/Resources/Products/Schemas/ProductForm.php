<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Text::make('Excel LİSTE / SatışFiyat sayfalarındaki malzeme kodları ve birim fiyatlar. Teklif ve sipariş kalemlerinde kullanılır.')
                    ->columnSpanFull(),

                TextInput::make('malzeme_kodu')
                    ->label('Malzeme Kodu')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Örn: CRN 100 001')
                    ->columnSpanFull(),

                TextInput::make('malzeme_aciklamasi')
                    ->label('Malzeme Açıklaması')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                self::decimalInput('uzunluk_m', 'Uzunluk (m)'),
                self::decimalInput('sac_kalinlik', 'Sac Kalınlık'),
                self::decimalInput('birim_kilo', 'Birim Kilo'),

                TextInput::make('birim')
                    ->label('Birim')
                    ->default('AD')
                    ->maxLength(20)
                    ->placeholder('AD, M, KG vb.')
                    ->dehydrateStateUsing(
                        fn($state) =>
                        filled($state) ? trim((string) $state) : 'AD'
                    )
                    ->helperText('Ölçü birimi: AD = Adet, M = Metre, KG = Kilogram.'),

                self::decimalInput('sac_fiyati', 'Sac Fiyatı'),
                self::decimalInput('izole_fiyati', 'İzole Fiyatı'),
                self::decimalInput('kilif_430_fiyati', '430 Kılıf Fiyatı'),
                self::decimalInput('fiyat_liste', 'Fiyat Liste (Satış)', default: 0),

                Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true),

            ]);
    }

    /**
     * Decimal alan: düz numeric input (Filament'in varsayılanı).
     * formatStateUsing yok → kayıttan gelen değer (örn. "5.0000") aynen görünür.
     */
    protected static function decimalInput(string $name, string $label, $default = null): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->inputMode('decimal')
            ->step(0.0001)
            ->default($default)
            ->nullable()
            ->dehydrated(fn ($state) => true)
            ->dehydrateStateUsing(function ($state) {
                if ($state === '' || $state === null) {
                    return null;
                }
                return str_replace(',', '.', (string) $state);
            });
    }
}

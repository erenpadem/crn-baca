<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'md' => 2,
                'xl' => 3,
            ])
            ->components([

                TextEntry::make('malzeme_kodu')
                    ->label('Malzeme Kodu'),

                TextEntry::make('malzeme_aciklamasi')
                    ->label('Malzeme Açıklaması')
                    ->columnSpanFull(),

                self::decimalEntry('uzunluk_m', 'Uzunluk (m)'),
                self::decimalEntry('sac_kalinlik', 'Sac Kalınlık'),
                self::decimalEntry('birim_kilo', 'Birim Kilo'),

                TextEntry::make('birim')
                    ->label('Birim')
                    ->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->getAttribute('birim'))
                    ->placeholder('–'),

                self::decimalEntry('sac_fiyati', 'Sac Fiyatı'),
                self::decimalEntry('izole_fiyati', 'İzole Fiyatı'),
                self::decimalEntry('kilif_430_fiyati', '430 Kılıf Fiyatı'),
                self::decimalEntry('fiyat_liste', 'Fiyat Liste (Satış)'),

                TextEntry::make('aktif')
                    ->label('Aktif')
                    ->badge()
                    ->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->getAttribute('aktif'))
                    ->formatStateUsing(fn ($state) => $state ? 'Evet' : 'Hayır'),
            ]);
    }

    protected static function decimalEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->getAttribute($name))
            ->formatStateUsing(function ($state) {
                if ($state === null || $state === '') {
                    return '–';
                }

                return number_format((float) $state, 4, ',', '.');
            })
            ->placeholder('–');
    }
}

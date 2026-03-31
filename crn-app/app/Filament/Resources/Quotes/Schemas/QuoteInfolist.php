<?php

namespace App\Filament\Resources\Quotes\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 1,
            ])
            ->components([
                Section::make('Teklif Bilgileri')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('teklif_no')->label('Teklif No')->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->teklif_no),
                        TextEntry::make('dealer.unvan')->label('Müşteri'),
                        TextEntry::make('durum')->label('Durum')->badge(),
                        TextEntry::make('proje_adi')->label('Proje Adı'),
                        TextEntry::make('cihaz_marka_model')->label('Cihaz Marka/Model'),
                        self::decimalEntry('musteri_iskonto_yuzde', 'Müşteri İskonto %'),
                        self::decimalEntry('musteri_net_tutar', 'Müşteri net tutar (KDV hariç)'),
                        TextEntry::make('musteri_not')->label('Müşteri notu')->placeholder('–')->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
                Section::make('Teklif Kalemleri')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('_kalem_yok_admin')
                            ->hiddenLabel()
                            ->getStateUsing(fn () => 'Bu teklifte kalem satırı yok.')
                            ->visible(function (Component $c): bool {
                                $q = $c->getContainer()->getRecord();

                                return $q instanceof \App\Models\Quote && $q->items()->doesntExist();
                            }),
                        RepeatableEntry::make('items')
                            ->visible(function (Component $c): bool {
                                $q = $c->getContainer()->getRecord();

                                return $q instanceof \App\Models\Quote && $q->items()->exists();
                            })
                            ->schema([
                                TextEntry::make('product.malzeme_kodu')->label('Malzeme Kodu'),
                                TextEntry::make('product.malzeme_aciklamasi')->label('Açıklama'),
                                TextEntry::make('product.birim')->label('Birim'),
                                self::decimalEntryFromRecord('birim_fiyat', 'Birim Fiyat'),
                                self::decimalEntryFromRecord('adet', 'Adet'),
                                self::decimalEntryFromRecord('musteri_maliyet_birim', 'Müşteri maliyet birim fiyatı (₺)'),
                                self::decimalEntryFromRecord('musteri_birim_fiyat', 'Müşteri satış birim fiyatı (₺)'),
                                self::decimalEntryFromRecord('tutar', 'Tutar'),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 3,
                                'lg' => 4,
                                'xl' => 6,
                                '2xl' => 8,
                            ]),
                    ]),
            ]);
    }

    protected static function decimalEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->getAttribute($name))
            ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '–')
            ->placeholder('–');
    }

    protected static function decimalEntryFromRecord(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->getAttribute($name))
            ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 4, ',', '.') : '–')
            ->placeholder('–');
    }
}

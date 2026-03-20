<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Models\Product;
use App\Models\Quote;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Teklif Bilgileri')
                    ->description('Excel TEKLİF HAZIRLAMA / Sipariş Formu başlığı: teklif no, siparişi veren firma (bayi), durum.')
                    ->schema([
                        TextInput::make('teklif_no')->label('Teklif No')->required()->maxLength(50)->default(fn () => 'T-' . now()->format('Ymd') . '-' . str_pad((string) (Quote::query()->count() + 1), 4, '0', STR_PAD_LEFT)),
                        Select::make('dealer_id')
                            ->label('Bayi / Müşteri')
                            ->relationship('dealer', 'unvan')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->noOptionsMessage('Bayi bulunamadı. Önce Bayiler sayfasından bayi ekleyin.'),
                        Select::make('durum')
                            ->label('Durum')
                            ->options([
                                'taslak' => 'Taslak',
                                'gonderildi' => 'Gönderildi',
                                'musteri_teklif_verdi' => 'Müşteri Teklif Verdi',
                                'onaylandi' => 'Onaylandı',
                                'reddedildi' => 'Reddedildi',
                            ])
                            ->default('taslak'),
                        TextInput::make('proje_adi')->label('Proje Adı')->maxLength(255),
                        TextInput::make('cihaz_marka_model')->label('Cihaz Marka - Model')->maxLength(255),
                        self::decimalInput('musteri_iskonto_yuzde', 'Müşteri İskonto %'),
                        self::decimalInput('musteri_net_tutar', 'Müşteri net tutar (KDV hariç)', null),
                    ])->columns(2),
                Section::make('Teklif Kalemleri')
                    ->description('Excel’deki malzeme kalemleri tablosu: Malzeme Kodu, Açıklama, Birim Fiyat, Adet, Tutar.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Ürün')
                                    ->relationship(
                                        'product',
                                        'malzeme_aciklamasi',
                                        fn ($q) => $q ? $q->where('aktif', true) : Product::query()->where('aktif', true)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->noOptionsMessage('Ürün bulunamadı. Önce Ürünler sayfasından ürün ekleyin.')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state && $p = Product::find($state)) {
                                            $set('birim_fiyat', $p->fiyat_liste);
                                        }
                                    }),
                                self::decimalInput('birim_fiyat', 'Birim Fiyat', 0)->required(),
                                self::decimalInput('adet', 'Adet', 1)->required(),
                                self::decimalInput('musteri_maliyet_birim', 'Müşteri maliyet birim', null),
                                self::decimalInput('musteri_birim_fiyat', 'Müşteri satış birim', null),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->addActionLabel('Kalem Ekle'),
                    ]),
            ]);
    }

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
            ->dehydrateStateUsing(function ($state) use ($default) {
                if ($state === '' || $state === null) {
                    return $default;
                }
                $v = str_replace(',', '.', (string) $state);
                return is_numeric($v) ? (float) $v : $default;
            });
    }
}

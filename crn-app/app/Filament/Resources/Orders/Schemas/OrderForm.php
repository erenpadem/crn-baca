<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public const CONTEXT_ADMIN = 'admin';

    public const CONTEXT_BAYI = 'bayi';

    public static function configure(Schema $schema, string $context = self::CONTEXT_ADMIN): Schema
    {
        $isBayi = $context === self::CONTEXT_BAYI;
        $ozellikAciklama = Order::ozellikKoduAciklamalari();

        return $schema
            ->components([
                Section::make($isBayi ? 'Teklif talebi' : 'Sipariş Bilgileri')
                    ->description($isBayi
                        ? 'Baca ölçüleri, istediğiniz iskonto ve ürün kalemleri. Kaydettikten sonra teklifi satışa gönderebilirsiniz; birim fiyatlar sizde görünmez (liste fiyatı arka planda kullanılır).'
                        : 'Ön sipariş no, tarih, proje; çizimdeki sipariş başlığı ile uyumludur.')
                    ->schema([
                        TextInput::make('siparis_no')
                            ->label($isBayi ? 'Talep no' : 'Sipariş No')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Örn: S-20260131-0001'),
                        TextInput::make('on_siparis_no')->label('Ön sipariş no')->maxLength(50),
                        $isBayi
                            ? Hidden::make('dealer_id')->default(fn () => Filament::getTenant()?->getKey())
                            : Select::make('dealer_id')
                                ->label('Bayi / Müşteri')
                                ->relationship('dealer', 'unvan')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->noOptionsMessage('Bayi bulunamadı. Önce Bayiler sayfasından bayi ekleyin.'),
                        Select::make('quote_id')
                            ->label('Teklif (opsiyonel)')
                            ->relationship('quote', 'teklif_no')
                            ->searchable()
                            ->preload()
                            ->noOptionsMessage('Teklif yok. Boş bırakılabilir.')
                            ->visible(fn () => ! $isBayi),
                        DatePicker::make('siparis_tarihi')->label('Sipariş Tarihi')->required()->default(now()),
                        TextInput::make('proje_adi')->label('Proje Adı')->maxLength(255),
                        TextInput::make('cihaz_marka_model')->label('Cihaz Marka/Model')->maxLength(255),
                        self::decimalInput('bac_cap_mm', 'Baca çapı (mm)', null),
                        self::decimalInput('bac_yukseklik_mm', 'Baca yüksekliği (mm)', null),
                        Select::make('yon')
                            ->label('Yön')
                            ->options([
                                'yatay' => 'Yatay',
                                'dikey' => 'Dikey',
                            ])
                            ->placeholder('Seçiniz'),
                        Section::make('Çizim / sipariş formu üzerindeki özellik kodları')
                            ->schema([
                                Toggle::make('attr_n')->label('N kodu geçerli')->helperText($ozellikAciklama['attr_n'])->inline(false),
                                Toggle::make('attr_m')->label('M kodu geçerli')->helperText($ozellikAciklama['attr_m'])->inline(false),
                                Toggle::make('attr_a')->label('A kodu geçerli')->helperText($ozellikAciklama['attr_a'])->inline(false),
                                Toggle::make('attr_h')->label('H kodu geçerli')->helperText($ozellikAciklama['attr_h'])->inline(false),
                                Toggle::make('attr_di')->label('DI kodu geçerli')->helperText($ozellikAciklama['attr_di'])->inline(false),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                        Select::make('durum')
                            ->label('Durum')
                            ->options($isBayi ? [
                                Order::DURUM_TASLAK => Order::durumEtiketi(Order::DURUM_TASLAK),
                            ] : [
                                Order::DURUM_TASLAK => Order::durumEtiketi(Order::DURUM_TASLAK),
                                Order::DURUM_MUSTERI_ONAYI_BEKLIYOR => Order::durumEtiketi(Order::DURUM_MUSTERI_ONAYI_BEKLIYOR),
                                Order::DURUM_MUSTERI_ONAYLANDI => Order::durumEtiketi(Order::DURUM_MUSTERI_ONAYLANDI),
                                Order::DURUM_YONETIM_ONAYI_BEKLIYOR => Order::durumEtiketi(Order::DURUM_YONETIM_ONAYI_BEKLIYOR),
                                Order::DURUM_BAYI_KARSI_TEKLIF_VERDI => Order::durumEtiketi(Order::DURUM_BAYI_KARSI_TEKLIF_VERDI),
                                Order::DURUM_URETICI_ONAYI_BEKLIYOR => Order::durumEtiketi(Order::DURUM_URETICI_ONAYI_BEKLIYOR),
                                Order::DURUM_ONAYLANDI => Order::durumEtiketi(Order::DURUM_ONAYLANDI),
                                Order::DURUM_BEKLEMEDE => Order::durumEtiketi(Order::DURUM_BEKLEMEDE),
                                Order::DURUM_IMALATTA => Order::durumEtiketi(Order::DURUM_IMALATTA),
                                Order::DURUM_TAMAMLANDI => Order::durumEtiketi(Order::DURUM_TAMAMLANDI),
                            ])
                            ->default($isBayi ? Order::DURUM_TASLAK : Order::DURUM_BEKLEMEDE)
                            ->required()
                            ->disabled(fn (?Order $record) => $isBayi && $record && $record->durum !== Order::DURUM_TASLAK),
                        self::decimalInput('iskonto_yuzde', 'İskonto %'),
                        Textarea::make('aciklama')->label('Açıklama')->rows(2)->columnSpanFull(),
                        Toggle::make('bayiye_fiyat_goster')
                            ->label('Bayi panelinde fiyat ve tutarları göster')
                            ->helperText('Maliyet / fiyatlandırma tamamlandığında açın; bayi talep detayında kalem fiyatları ve hesap özetini görür.')
                            ->visible(fn () => ! $isBayi)
                            ->columnSpanFull(),
                    ])->columns(3),
                Section::make('Kur ve ön / nihai tutar (KDV hariç)')
                    ->description('Boş bırakılan tutarlar kalemler ve iskontodan otomatik hesaplanır. Kur farkı % örneği: 10 → taban tutara %10 ekler.')
                    ->visible(fn () => ! $isBayi)
                    ->schema([
                        self::decimalInput('kur', 'Kur', null),
                        self::decimalInput('kur_farki_yuzde', 'Kur farkı %', 10),
                        self::decimalInput('tutar_kdvsiz_on', 'Ön tutar (KDV hariç, manuel)', null),
                        self::decimalInput('tutar_kdvsiz_nihai', 'Nihai tutar (KDV hariç, manuel)', null),
                    ])->columns(2),
                Section::make('Opsiyonel hizmetler')
                    ->description('Anahtarı açınca ilgili tutar alanı görünür ve genel toplama (KDV öncesi) eklenir. Kapalı bırakırsanız o hizmet hesaba katılmaz. Yukarıdaki N / M / A / H / DI kodlarından farklıdır; onlar sadece çizimdeki işaretlerin kaydıdır.')
                    ->visible(fn () => ! $isBayi)
                    ->schema([
                        Toggle::make('opsiyonel_nakliye')->label('Nakliye')->live(),
                        self::decimalInput('nakliye_tutari', 'Nakliye tutarı', null)->visible(fn ($get) => $get('opsiyonel_nakliye')),
                        Toggle::make('opsiyonel_akreditif')->label('Akreditif')->live(),
                        self::decimalInput('akreditif_tutari', 'Akreditif tutarı', null)->visible(fn ($get) => $get('opsiyonel_akreditif')),
                        Toggle::make('opsiyonel_montaj')->label('Montaj')->live(),
                        self::decimalInput('montaj_tutari', 'Montaj tutarı', null)->visible(fn ($get) => $get('opsiyonel_montaj')),
                        Toggle::make('opsiyonel_havalandirma')->label('Havalandırma')->live(),
                        self::decimalInput('havalandirma_tutari', 'Havalandırma tutarı', null)->visible(fn ($get) => $get('opsiyonel_havalandirma')),
                        Toggle::make('opsiyonel_diger')->label('Diğer')->live(),
                        self::decimalInput('diger_tutari', 'Diğer tutar', null)->visible(fn ($get) => $get('opsiyonel_diger')),
                        TextInput::make('diger_aciklama')->label('Diğer açıklama')->maxLength(500)->visible(fn ($get) => $get('opsiyonel_diger'))->columnSpanFull(),
                    ])->columns(2),
                Section::make('KDV ve onay')
                    ->schema([
                        ...($isBayi
                            ? [Hidden::make('kdv_orani')->default(20)->dehydrated()]
                            : [self::decimalInput('kdv_orani', 'KDV %', 20)]),
                        Checkbox::make('kvkk_onay')->label('Aydınlatma metni / sipariş onayı (KVKK)'),
                    ])
                    ->columns(2),
                Section::make('Üretici / seri (yönetim)')
                    ->visible(fn () => ! $isBayi)
                    ->schema([
                        TextInput::make('seri_no')->label('Seri (S)')->maxLength(80),
                        TextInput::make('yeni_seri_no')->label('Yeni seri')->maxLength(80),
                        DatePicker::make('yeni_seri_tarihi')->label('Yeni seri tarihi'),
                    ])->columns(3),
                Section::make('Sipariş Kalemleri')
                    ->description($isBayi
                        ? 'Ürün seçin ve adet girin. Birim fiyat bayi ekranında gösterilmez; sistem liste fiyatını kullanır.'
                        : 'Ürün listesinden adetli seçim; tutar satır bazında hesaplanır.')
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
                                $isBayi
                                    ? Hidden::make('birim_fiyat')
                                        ->default(0)
                                        ->dehydrated()
                                    : self::decimalInput('birim_fiyat', 'Birim Fiyat', 0)->required(),
                                self::decimalInput('adet', 'Adet', 1)->required(),
                            ])
                            ->columns($isBayi ? 2 : 3)
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

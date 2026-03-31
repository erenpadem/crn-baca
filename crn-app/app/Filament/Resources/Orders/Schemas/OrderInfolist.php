<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OrderInfolist
{
    public const CONTEXT_ADMIN = 'admin';

    public const CONTEXT_BAYI = 'bayi';

    public static function configure(Schema $schema, string $context = self::CONTEXT_ADMIN): Schema
    {
        $isBayi = $context === self::CONTEXT_BAYI;

        return $schema
            ->columns([
                'default' => 1,
                'lg' => 1,
            ])
            ->components([
                Section::make($isBayi ? 'Teklif talebi' : 'Sipariş Bilgileri')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('siparis_no')->label($isBayi ? 'Talep no' : 'Sipariş No')->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->siparis_no),
                        TextEntry::make('on_siparis_no')->label('Ön sipariş no')->placeholder('–'),
                        TextEntry::make('dealer.unvan')->label('Müşteri')
                            ->visible(function () use ($isBayi) {
                                if ($isBayi) {
                                    return false;
                                }
                                $user = Auth::user();

                                return ! ($user instanceof User && $user->hasRole('imalathane'));
                            }),
                        TextEntry::make('siparis_tarihi')->label('Tarih')->date('d.m.Y'),
                        TextEntry::make('durum')->label('Durum')->badge()
                            ->formatStateUsing(fn ($state) => Order::durumEtiketi($state)),
                        TextEntry::make('proje_adi')->label('Proje Adı'),
                        TextEntry::make('cihaz_marka_model')->label('Cihaz Marka/Model'),
                        self::decimalEntry('bac_cap_mm', 'Baca çapı (mm)'),
                        self::decimalEntry('bac_yukseklik_mm', 'Baca yüksekliği (mm)'),
                        TextEntry::make('yon')
                            ->label('Yön')
                            ->badge()
                            ->getConstantStateUsing(function (Component $c): string {
                                $record = $c->getContainer()->getRecord();
                                if (! $record instanceof Order) {
                                    return '–';
                                }
                                $s = $record->getAttribute('yon');

                                return match ($s) {
                                    'yatay' => 'Yatay',
                                    'dikey' => 'Dikey',
                                    null, '' => '–',
                                    default => is_string($s) ? $s : (string) $s,
                                };
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'Yatay' => 'info',
                                'Dikey' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('ozellik_etiketleri')->label('Çizim / form özellik kodları (hangileri geçerli)')
                            ->getStateUsing(function (?Order $record): array {
                                if (! $record) {
                                    return [];
                                }
                                $satirlar = [];
                                foreach (Order::ozellikKoduAciklamalari() as $attr => $metin) {
                                    if ($record->{$attr}) {
                                        $satirlar[] = $metin;
                                    }
                                }

                                return $satirlar;
                            })
                            ->bulleted()
                            ->placeholder('–')
                            ->columnSpanFull(),
                        self::decimalEntry('iskonto_yuzde', 'İskonto %'),
                        TextEntry::make('aciklama')->label('Açıklama')->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->aciklama)->placeholder('–'),
                        TextEntry::make('kvkk_onay')->label('KVKK / onay')
                            ->visible(fn () => $isBayi)
                            ->formatStateUsing(fn ($s) => $s ? 'Evet' : 'Hayır'),
                        TextEntry::make('bayi_fiyat_bekliyor_bilgi')
                            ->hiddenLabel()
                            ->visible(function (Component $c) use ($isBayi): bool {
                                if (! $isBayi) {
                                    return false;
                                }
                                $order = self::orderFromInfolistComponent($c);

                                return $order instanceof Order && ! $order->bayiye_fiyat_goster;
                            })
                            ->getConstantStateUsing(fn (): string => 'Birim fiyat ve maliyet özeti, satış ekibi admin panelinde “Firma panelinde fiyat ve tutarları göster” seçeneğini işaretleyene kadar burada gösterilmez.')
                            ->color('gray')
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
                Section::make('Kur ve tutarlar (KDV hariç)')
                    ->columnSpanFull()
                    ->visible(fn (Component $c) => ! $isBayi || self::bayiPricingUnlocked($c))
                    ->schema([
                        self::decimalEntry('kur', 'Kur'),
                        self::decimalEntry('kur_farki_yuzde', 'Kur farkı %'),
                        self::decimalEntry('tutar_kdvsiz_on', 'Ön tutar (manuel)'),
                        self::decimalEntry('tutar_kdvsiz_nihai', 'Nihai tutar (manuel)'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Opsiyonel hizmetler')
                    ->columnSpanFull()
                    ->visible(fn (Component $c) => ! $isBayi || self::bayiPricingUnlocked($c))
                    ->schema([
                        TextEntry::make('nakliye_ozet')->label('Nakliye')
                            ->getStateUsing(fn (?Order $record): string => $record && $record->opsiyonel_nakliye ? self::fmtMoney((float) $record->nakliye_tutari) : '—'),
                        TextEntry::make('akreditif_ozet')->label('Akreditif')
                            ->getStateUsing(fn (?Order $record): string => $record && $record->opsiyonel_akreditif ? self::fmtMoney((float) $record->akreditif_tutari) : '—'),
                        TextEntry::make('montaj_ozet')->label('Montaj')
                            ->getStateUsing(fn (?Order $record): string => $record && $record->opsiyonel_montaj ? self::fmtMoney((float) $record->montaj_tutari) : '—'),
                        TextEntry::make('havalandirma_ozet')->label('Havalandırma')
                            ->getStateUsing(fn (?Order $record): string => $record && $record->opsiyonel_havalandirma ? self::fmtMoney((float) $record->havalandirma_tutari) : '—'),
                        TextEntry::make('diger_ozet')->label('Diğer')
                            ->getStateUsing(function (?Order $record): string {
                                if (! $record || ! $record->opsiyonel_diger) {
                                    return '—';
                                }
                                $t = self::fmtMoney((float) $record->diger_tutari);
                                $a = $record->diger_aciklama ? " ({$record->diger_aciklama})" : '';

                                return $t.$a;
                            }),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Hesap özeti')
                    ->columnSpanFull()
                    ->visible(fn (Component $c) => ! $isBayi || self::bayiPricingUnlocked($c))
                    ->schema([
                        TextEntry::make('kalem_net_kdvsiz')->label('Kalem toplamı (iskonto sonrası, KDV hariç)')
                            ->formatStateUsing(fn ($s) => self::fmtMoney((float) $s)),
                        TextEntry::make('hesaplanan_kdvsiz_on')->label('Ön tutar (hesaplanan)')
                            ->formatStateUsing(fn ($s) => self::fmtMoney((float) $s)),
                        TextEntry::make('hesaplanan_kdvsiz_nihai')->label('Nihai taban (kur farkı sonrası)')
                            ->formatStateUsing(fn ($s) => self::fmtMoney((float) $s)),
                        TextEntry::make('ara_toplam_kdvsiz')->label('Ara toplam (KDV hariç)')
                            ->formatStateUsing(fn ($s) => self::fmtMoney((float) $s)),
                        TextEntry::make('kdv_tutari')->label('KDV tutarı')
                            ->formatStateUsing(fn ($s) => self::fmtMoney((float) $s)),
                        TextEntry::make('genel_toplam')->label('Genel toplam')
                            ->formatStateUsing(fn ($s) => self::fmtMoney((float) $s))->weight('bold'),
                        TextEntry::make('kdv_orani')->label('KDV %')->formatStateUsing(fn ($s) => $s !== null ? number_format((float) $s, 2, ',', '.') : '20'),
                        TextEntry::make('kvkk_onay')->label('KVKK / sipariş onayı')->formatStateUsing(fn ($s) => $s ? 'Evet' : 'Hayır'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Bayi karşı teklifi')
                    ->columnSpanFull()
                    ->visible(function (Component $c): bool {
                        $order = self::orderFromInfolistComponent($c);

                        return $order instanceof Order && self::orderHasBayiKarsiData($order);
                    })
                    ->schema([
                        self::decimalEntry('bayi_karsi_iskonto_yuzde', 'Karşı teklif iskonto %'),
                        TextEntry::make('bayi_karsi_not')->label('Bayi notu')
                            ->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->bayi_karsi_not)
                            ->placeholder('–')
                            ->columnSpanFull(),
                        TextEntry::make('bayi_karsi_gonderim_at')->label('Karşı teklif zamanı')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('–'),
                        TextEntry::make('bayi_karsi_kalem_tutar')->label('Karşı teklif kalem tutarı (KDV hariç)')
                            ->getStateUsing(function (Component $c): string {
                                $order = self::orderFromInfolistComponent($c);
                                if (! $order instanceof Order) {
                                    return '—';
                                }
                                $order->loadMissing('items');
                                $t = 0.0;
                                foreach ($order->items as $item) {
                                    if ($item->bayi_karsi_birim_fiyat === null) {
                                        continue;
                                    }
                                    $t += (float) $item->bayi_karsi_birim_fiyat * (float) $item->adet;
                                }

                                return $t > 0 ? self::fmtMoney($t) : '—';
                            }),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Üretici / seri')
                    ->columnSpanFull()
                    ->visible(fn () => ! $isBayi)
                    ->schema([
                        TextEntry::make('seri_no')->label('Seri (S)')->placeholder('–'),
                        TextEntry::make('yeni_seri_no')->label('Yeni seri')->placeholder('–'),
                        TextEntry::make('yeni_seri_tarihi')->label('Yeni seri tarihi')->date('d.m.Y')->placeholder('–'),
                        TextEntry::make('imalat_listesi_cikti_at')->label('İmalat listesi')->dateTime('d.m.Y H:i')->placeholder('Henüz oluşturulmadı'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
                Section::make('Malzeme ve Gereksinimler')
                    ->columnSpanFull()
                    ->description($isBayi
                        ? 'Ürün kalemleri; fiyat sütunları satış paylaştığında görünür.'
                        : 'İmalathane için üretim kalemleri (tablo: kod, ölçüler, adet ve fiyatlar).')
                    ->schema([
                        self::orderItemsRepeatable($isBayi),
                    ]),
            ]);
    }

    protected static function orderItemsRepeatable(bool $isBayi): RepeatableEntry
    {
        if ($isBayi) {
            return RepeatableEntry::make('items')
                ->columnSpanFull()
                ->table([
                    TableColumn::make('Malzeme kodu'),
                    TableColumn::make('Açıklama'),
                    TableColumn::make('Birim'),
                    TableColumn::make('Adet'),
                    TableColumn::make('Birim fiyat'),
                    TableColumn::make('Tutar'),
                    TableColumn::make('Karşı teklif birim'),
                ])
                ->schema([
                    TextEntry::make('product.malzeme_kodu')->hiddenLabel()->placeholder('–'),
                    TextEntry::make('product.malzeme_aciklamasi')->hiddenLabel()->placeholder('–'),
                    TextEntry::make('product.birim')->hiddenLabel()->placeholder('–'),
                    self::decimalEntryFromRecord('adet', 'Adet')->hiddenLabel(),
                    self::decimalEntryFromRecord('birim_fiyat', 'Birim Fiyat')
                        ->hiddenLabel()
                        ->visible(fn (Component $c) => self::bayiPricingUnlocked($c)),
                    self::decimalEntryFromRecord('tutar', 'Tutar')
                        ->hiddenLabel()
                        ->visible(fn (Component $c) => self::bayiPricingUnlocked($c)),
                    self::decimalEntryFromRecord('bayi_karsi_birim_fiyat', 'Karşı teklif birim')
                        ->hiddenLabel()
                        ->visible(fn (Component $c) => self::showBayiKarsiBirimInItemsColumn($c, true)),
                ]);
        }

        return RepeatableEntry::make('items')
            ->columnSpanFull()
            ->table([
                TableColumn::make('Malzeme kodu'),
                TableColumn::make('Açıklama'),
                TableColumn::make('Birim'),
                TableColumn::make('Adet'),
                TableColumn::make('Birim fiyat'),
                TableColumn::make('Tutar'),
                TableColumn::make('Karşı teklif birim'),
                TableColumn::make('Uzunluk (m)'),
                TableColumn::make('Sac kalınlık'),
            ])
            ->schema([
                TextEntry::make('product.malzeme_kodu')->hiddenLabel()->placeholder('–'),
                TextEntry::make('product.malzeme_aciklamasi')->hiddenLabel()->placeholder('–'),
                TextEntry::make('product.birim')->hiddenLabel()->placeholder('–'),
                self::decimalEntryFromRecord('adet', 'Adet')->hiddenLabel(),
                self::decimalEntryFromRecord('birim_fiyat', 'Birim Fiyat')->hiddenLabel(),
                self::decimalEntryFromRecord('tutar', 'Tutar')->hiddenLabel(),
                self::decimalEntryFromRecord('bayi_karsi_birim_fiyat', 'Karşı teklif birim')->hiddenLabel(),
                self::decimalEntryFromRelation('product.uzunluk_m', 'Uzunluk (m)')->hiddenLabel(),
                self::decimalEntryFromRelation('product.sac_kalinlik', 'Sac kalınlık')->hiddenLabel(),
            ]);
    }

    protected static function orderFromInfolistComponent(Component $c): ?Order
    {
        $record = $c->getContainer()->getRecord();
        if ($record instanceof Order) {
            return $record;
        }
        if ($record instanceof OrderItem) {
            return $record->order;
        }

        return null;
    }

    protected static function bayiPricingUnlocked(Component $c): bool
    {
        $order = self::orderFromInfolistComponent($c);

        return $order instanceof Order && $order->bayiye_fiyat_goster;
    }

    protected static function orderHasBayiKarsiData(Order $order): bool
    {
        if ($order->bayi_karsi_gonderim_at) {
            return true;
        }
        if ($order->bayi_karsi_iskonto_yuzde !== null && (string) $order->bayi_karsi_iskonto_yuzde !== '') {
            return true;
        }
        if (filled($order->bayi_karsi_not)) {
            return true;
        }
        $order->loadMissing('items');
        foreach ($order->items as $item) {
            if ($item->bayi_karsi_birim_fiyat !== null && (string) $item->bayi_karsi_birim_fiyat !== '') {
                return true;
            }
        }

        return false;
    }

    protected static function showBayiKarsiBirimInItemsColumn(Component $c, bool $isBayi): bool
    {
        if (! $isBayi) {
            return true;
        }
        $order = self::orderFromInfolistComponent($c);
        if (! $order instanceof Order || ! $order->bayiye_fiyat_goster) {
            return false;
        }
        if ($order->durum === Order::DURUM_TASLAK) {
            return false;
        }

        return true;
    }

    protected static function fmtMoney(float $n): string
    {
        return number_format($n, 2, ',', '.').' ₺';
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

    protected static function decimalEntryFromRelation(string $path, string $label): TextEntry
    {
        return TextEntry::make($path)
            ->label($label)
            ->getConstantStateUsing(function (Component $c) use ($path) {
                $record = $c->getContainer()->getRecord();

                return $record ? data_get($record, $path) : null;
            })
            ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 4, ',', '.') : '–')
            ->placeholder('–');
    }
}

<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('malzeme_kodu')
                ->label('Malzeme kodu')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['malzeme_kodu', 'Malzeme Kodu', 'kod', 'KOD', 'SKU']),
            ImportColumn::make('malzeme_aciklamasi')
                ->label('Malzeme açıklaması')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['malzeme_aciklamasi', 'Malzeme Açıklaması', 'aciklama', 'Açıklama']),
            ImportColumn::make('uzunluk_m')
                ->label('Uzunluk (m)')
                ->numeric()
                ->ignoreBlankState()
                ->guess(['uzunluk_m', 'Uzunluk (m)', 'uzunluk']),
            ImportColumn::make('sac_kalinlik')
                ->label('Sac kalınlık')
                ->numeric()
                ->ignoreBlankState()
                ->guess(['sac_kalinlik', 'Sac Kalınlık', 'kalınlık']),
            ImportColumn::make('birim_kilo')
                ->label('Birim kilo')
                ->numeric()
                ->ignoreBlankState()
                ->guess(['birim_kilo', 'Birim Kilo']),
            ImportColumn::make('birim')
                ->label('Birim')
                ->ignoreBlankState()
                ->guess(['birim', 'Birim']),
            ImportColumn::make('sac_fiyati')
                ->label('Sac fiyatı')
                ->numeric()
                ->ignoreBlankState()
                ->guess(['sac_fiyati', 'Sac Fiyatı']),
            ImportColumn::make('izole_fiyati')
                ->label('İzole fiyatı')
                ->numeric()
                ->ignoreBlankState()
                ->guess(['izole_fiyati', 'İzole Fiyatı']),
            ImportColumn::make('kilif_430_fiyati')
                ->label('430 kılıf fiyatı')
                ->numeric()
                ->ignoreBlankState()
                ->guess(['kilif_430_fiyati', '430 Kılıf', 'kilif_430']),
            ImportColumn::make('fiyat_liste')
                ->label('Fiyat liste (satış)')
                ->numeric()
                ->ignoreBlankState()
                ->guess(['fiyat_liste', 'Fiyat Liste', 'liste_fiyat', 'Liste Fiyat']),
            ImportColumn::make('aktif')
                ->label('Aktif')
                ->ignoreBlankState()
                ->castStateUsing(function (mixed $originalState, mixed $state): ?bool {
                    if ($originalState === null || trim((string) $originalState) === '') {
                        return null;
                    }
                    $s = mb_strtolower(trim((string) $originalState));

                    return match ($s) {
                        '1', 'true', 'yes', 'y', 'on', 'evet', 'e' => true,
                        '0', 'false', 'no', 'n', 'off', 'hayır', 'hayir', 'h', 'pasif' => false,
                        default => filter_var($s, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                    };
                })
                ->guess(['aktif', 'Aktif', 'active']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $kod = isset($this->data['malzeme_kodu']) ? trim((string) $this->data['malzeme_kodu']) : '';
        if ($kod === '') {
            return new Product;
        }

        return Product::firstOrNew(['malzeme_kodu' => $kod]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Ürün içe aktarımı tamamlandı. '.Number::format($import->successful_rows).' satır işlendi.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' satır hata verdi.';
        }

        return $body;
    }
}

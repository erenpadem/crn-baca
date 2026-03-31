<?php

namespace App\Filament\Imports;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class QuoteItemImporter extends Importer
{
    protected static ?string $model = QuoteItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('teklif_no')->label('Teklif No')->requiredMapping()->rules(['required']),
            ImportColumn::make('malzeme_kodu')->label('Malzeme Kodu')->requiredMapping()->rules(['required']),
            ImportColumn::make('birim_fiyat')->label('Birim Fiyat (iç)')->numeric()->requiredMapping()->rules(['required', 'numeric']),
            ImportColumn::make('adet')->label('Adet')->numeric()->ignoreBlankState()->rules(['nullable', 'numeric']),
            ImportColumn::make('musteri_maliyet_birim')->label('Müşteri maliyet birim')->numeric()->ignoreBlankState()->rules(['nullable', 'numeric']),
            ImportColumn::make('musteri_birim_fiyat')->label('Müşteri satış birim')->numeric()->ignoreBlankState()->rules(['nullable', 'numeric']),
        ];
    }

    public function resolveRecord(): ?QuoteItem
    {
        $quote = Quote::where('teklif_no', $this->data['teklif_no'] ?? '')->first();
        if (! $quote) {
            throw ValidationException::withMessages(['teklif_no' => 'Teklif bulunamadı: '.($this->data['teklif_no'] ?? '')]);
        }
        $product = Product::where('malzeme_kodu', $this->data['malzeme_kodu'] ?? '')->first();
        if (! $product) {
            throw ValidationException::withMessages(['malzeme_kodu' => 'Ürün bulunamadı: '.($this->data['malzeme_kodu'] ?? '')]);
        }

        $row = [
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'birim_fiyat' => (float) ($this->data['birim_fiyat'] ?? 0),
            'adet' => (float) ($this->data['adet'] ?? 1),
        ];
        if (isset($this->data['musteri_maliyet_birim']) && $this->data['musteri_maliyet_birim'] !== '' && $this->data['musteri_maliyet_birim'] !== null) {
            $row['musteri_maliyet_birim'] = (float) $this->data['musteri_maliyet_birim'];
        }
        if (isset($this->data['musteri_birim_fiyat']) && $this->data['musteri_birim_fiyat'] !== '' && $this->data['musteri_birim_fiyat'] !== null) {
            $row['musteri_birim_fiyat'] = (float) $this->data['musteri_birim_fiyat'];
        }

        return new QuoteItem($row);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Teklif kalemi içe aktarımı tamamlandı. '.Number::format($import->successful_rows).' kalem aktarıldı.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' satır hata verdi.';
        }

        return $body;
    }
}

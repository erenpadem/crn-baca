<?php

namespace App\Filament\Imports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class OrderItemImporter extends Importer
{
    protected static ?string $model = OrderItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('siparis_no')->label('Sipariş No')->requiredMapping()->rules(['required']),
            ImportColumn::make('malzeme_kodu')->label('Malzeme Kodu')->requiredMapping()->rules(['required']),
            ImportColumn::make('birim_fiyat')->label('Birim Fiyat')->numeric()->requiredMapping()->rules(['required', 'numeric']),
            ImportColumn::make('adet')->label('Adet')->numeric()->ignoreBlankState()->rules(['nullable', 'numeric']),
            ImportColumn::make('bayi_karsi_birim_fiyat')->label('Karşı teklif birim fiyat')->numeric()->ignoreBlankState()->rules(['nullable', 'numeric']),
        ];
    }

    public function resolveRecord(): ?OrderItem
    {
        $order = Order::where('siparis_no', $this->data['siparis_no'] ?? '')->first();
        if (! $order) {
            throw ValidationException::withMessages(['siparis_no' => 'Sipariş bulunamadı: '.($this->data['siparis_no'] ?? '')]);
        }
        $product = Product::where('malzeme_kodu', $this->data['malzeme_kodu'] ?? '')->first();
        if (! $product) {
            throw ValidationException::withMessages(['malzeme_kodu' => 'Ürün bulunamadı: '.($this->data['malzeme_kodu'] ?? '')]);
        }

        $row = [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'birim_fiyat' => (float) ($this->data['birim_fiyat'] ?? 0),
            'adet' => (float) ($this->data['adet'] ?? 1),
        ];
        if (isset($this->data['bayi_karsi_birim_fiyat']) && $this->data['bayi_karsi_birim_fiyat'] !== '' && $this->data['bayi_karsi_birim_fiyat'] !== null) {
            $row['bayi_karsi_birim_fiyat'] = (float) $this->data['bayi_karsi_birim_fiyat'];
        }

        return new OrderItem($row);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Sipariş kalemi içe aktarımı tamamlandı. '.Number::format($import->successful_rows).' kalem aktarıldı.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' satır hata verdi.';
        }

        return $body;
    }
}

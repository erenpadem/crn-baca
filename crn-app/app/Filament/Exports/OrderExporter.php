<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    /** Kuyruk kullanmadan anında çalıştır; tamamlanınca bildirimde indirme butonları görünsün. */
    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('siparis_no')->label('Sipariş No'),
            ExportColumn::make('on_siparis_no')->label('Ön sipariş no'),
            ExportColumn::make('dealer.unvan')->label('Müşteri'),
            ExportColumn::make('dealer.firma_no')->label('Firma No'),
            ExportColumn::make('siparis_tarihi')->label('Tarih')->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d.m.Y') : ''),
            ExportColumn::make('proje_adi')->label('Proje'),
            ExportColumn::make('durum')->label('Durum')->formatStateUsing(fn ($state) => Order::durumEtiketi($state)),
            ExportColumn::make('items_count')->label('Kalem sayısı')->counts('items'),
            ExportColumn::make('genel_toplam')->label('Genel toplam (KDV dahil)')->state(fn (Order $r) => $r->genel_toplam),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Sipariş dışa aktarımı tamamlandı. '.Number::format($export->successful_rows).' kayıt aktarıldı.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' kayıt hata verdi.';
        }

        return $body;
    }
}

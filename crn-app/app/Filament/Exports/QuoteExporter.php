<?php

namespace App\Filament\Exports;

use App\Models\Quote;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class QuoteExporter extends Exporter
{
    protected static ?string $model = Quote::class;

    /** Kuyruk kullanmadan anında çalıştır; tamamlanınca bildirimde indirme butonları görünsün. */
    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('teklif_no')->label('Teklif No'),
            ExportColumn::make('dealer.unvan')->label('Müşteri'),
            ExportColumn::make('dealer.firma_no')->label('Firma No'),
            ExportColumn::make('proje_adi')->label('Proje'),
            ExportColumn::make('durum')->label('Durum')->formatStateUsing(function ($state) {
                return match ((string) $state) {
                    Quote::DURUM_TASLAK => 'Taslak',
                    Quote::DURUM_GONDERILDI => 'Gönderildi',
                    Quote::DURUM_MUSTERI_TEKLIF_VERDI => 'Müşteri Teklif Verdi',
                    Quote::DURUM_ONAYLANDI => 'Onaylandı',
                    Quote::DURUM_REDDEDILDI => 'Reddedildi',
                    default => filled($state) ? (string) $state : 'Taslak',
                };
            }),
            ExportColumn::make('created_at')->label('Tarih')->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d.m.Y') : ''),
            ExportColumn::make('musteri_iskonto_yuzde')->label('Müşteri iskonto %'),
            ExportColumn::make('musteri_net_tutar')->label('Müşteri net (KDV hariç)'),
            ExportColumn::make('items_count')->label('Kalem sayısı')->counts('items'),
            ExportColumn::make('toplam_tutar')->label('Kalem toplamı (KDV hariç)')->state(fn (Quote $r) => $r->items->sum('tutar')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Teklif dışa aktarımı tamamlandı. '.Number::format($export->successful_rows).' kayıt aktarıldı.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' kayıt hata verdi.';
        }

        return $body;
    }
}

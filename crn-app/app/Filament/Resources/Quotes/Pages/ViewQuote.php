<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quote;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $record->load(['dealer', 'items.product']);
        $actions = [];

        if (! auth()->user()?->hasRole('imalathane')) {
            if ($record->durum === Quote::DURUM_TASLAK) {
                $actions[] = Action::make('musteriye_gonder')
                    ->label('Müşteriye Gönder')
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->modalHeading('Teklifi müşteriye göndermek istediğinize emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update([
                            'durum' => Quote::DURUM_GONDERILDI,
                            'gonderim_tarihi' => now(),
                        ]);
                        Notification::make()->title('Teklif müşteriye gönderildi.')->success()->send();
                    });
            }

            if ($record->durum === Quote::DURUM_MUSTERI_TEKLIF_VERDI) {
                $actions[] = Action::make('musteri_teklifini_kabul')
                    ->label('Müşteri Teklifini Kabul Et')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->modalHeading('Müşteri teklifini kabul ediyor musunuz?')
                    ->action(function () use ($record) {
                        $record->update(['durum' => Quote::DURUM_ONAYLANDI]);
                        Notification::make()->title('Müşteri teklifi kabul edildi.')->success()->send();
                    });
                $actions[] = Action::make('musteri_teklifini_reddet')
                    ->label('Müşteri Teklifini Reddet')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->modalHeading('Müşteri teklifini reddediyor musunuz?')
                    ->action(function () use ($record) {
                        $record->update(['durum' => Quote::DURUM_REDDEDILDI]);
                        Notification::make()->title('Müşteri teklifi reddedildi.')->warning()->send();
                    });
            }

            if ($record->durum === Quote::DURUM_ONAYLANDI && $record->dealer_id) {
                $siparisNo = 'S-' . now()->format('Ymd') . '-' . str_pad((string) (Order::query()->count() + 1), 4, '0', STR_PAD_LEFT);
                $actions[] = Action::make('imalathane_yonlendir')
                    ->label('İmalathane\'ye Yönlendir')
                    ->color('primary')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->requiresConfirmation()
                    ->modalHeading('Sipariş oluşturup imalathane\'ye yönlendirilecek. Onaylıyor musunuz?')
                    ->modalDescription('Teklif kalemleri siparişe aktarılacak. Müşteri teklifi varsa o fiyatlar kullanılacak.')
                    ->action(function () use ($record, $siparisNo) {
                        $order = Order::create([
                            'siparis_no' => $siparisNo,
                            'dealer_id' => $record->dealer_id,
                            'quote_id' => $record->id,
                            'created_by' => auth()->id(),
                            'siparis_tarihi' => now(),
                            'proje_adi' => $record->proje_adi,
                            'cihaz_marka_model' => $record->cihaz_marka_model,
                            'durum' => 'beklemede',
                            'iskonto_yuzde' => $record->musteri_iskonto_yuzde,
                        ]);
                        foreach ($record->items as $item) {
                            $birimFiyat = $item->musteri_birim_fiyat ?? $item->musteri_maliyet_birim ?? $item->birim_fiyat;
                            $tutar = round((float) $birimFiyat * (float) $item->adet, 4);
                            OrderItem::create([
                                'order_id' => $order->id,
                                'product_id' => $item->product_id,
                                'birim_fiyat' => $birimFiyat,
                                'adet' => $item->adet,
                                'tutar' => $tutar,
                            ]);
                        }
                        Notification::make()->title('Sipariş oluşturuldu: ' . $siparisNo)->success()->send();
                        $this->redirect(OrderResource::getUrl('view', ['record' => $order]));
                    });
            }

            $actions[] = Action::make('export_excel')
                ->label('Excel İndir')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('admin.quotes.export-excel', ['id' => $record->id], absolute: true))
                ->openUrlInNewTab();
            $actions[] = Action::make('export_csv')
                ->label('CSV İndir')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(route('admin.quotes.export-csv', ['id' => $record->id], absolute: true))
                ->openUrlInNewTab();
            $actions[] = Action::make('export_pdf')
                ->label('PDF İndir')
                ->icon('heroicon-o-document')
                ->color('gray')
                ->url(route('admin.quotes.export-pdf', ['id' => $record->id], absolute: true))
                ->openUrlInNewTab();
            $actions[] = EditAction::make();
        }

        return $actions;
    }
}

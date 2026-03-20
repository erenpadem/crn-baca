<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $actions = [];
        $isImalathane = auth()->user()?->hasRole('imalathane');

        if ($isImalathane) {
            if (in_array($record->durum, [Order::DURUM_BEKLEMEDE, Order::DURUM_ONAYLANDI], true)) {
                $actions[] = Action::make('uretime_al')
                    ->label('Üretime Al')
                    ->color('success')
                    ->icon('heroicon-o-play')
                    ->requiresConfirmation()
                    ->modalHeading('Sipariş üretime alınacak')
                    ->action(function () use ($record) {
                        $record->update(['durum' => Order::DURUM_IMALATTA]);
                        Notification::make()->title('Sipariş üretime alındı.')->success()->send();
                    });
            }
            if ($record->durum === Order::DURUM_IMALATTA) {
                $actions[] = Action::make('tamamlandi')
                    ->label('Tamamlandı')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Sipariş tamamlandı olarak işaretlenecek')
                    ->action(function () use ($record) {
                        $record->update(['durum' => Order::DURUM_TAMAMLANDI]);
                        Notification::make()->title('Sipariş tamamlandı.')->success()->send();
                    });
            }
        } else {
            if (in_array($record->durum, [Order::DURUM_YONETIM_ONAYI_BEKLIYOR, Order::DURUM_BAYI_KARSI_TEKLIF_VERDI], true)) {
                $actions[] = Action::make('ureticiye_gonder')
                    ->label('Üretici onayına gönder')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function () use ($record) {
                        $record->update(['durum' => Order::DURUM_URETICI_ONAYI_BEKLIYOR]);
                        Notification::make()->title('Üretici onayı bekleniyor.')->success()->send();
                    });
                $actions[] = Action::make('yonetim_onayla')
                    ->label('Yönetim onayı (nihai)')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () use ($record) {
                        $record->update(['durum' => Order::DURUM_ONAYLANDI]);
                        Notification::make()->title('Sipariş onaylandı.')->success()->send();
                    });
            }
            if ($record->durum === Order::DURUM_URETICI_ONAYI_BEKLIYOR) {
                $actions[] = Action::make('uretici_onaylandi')
                    ->label('Üretici onayı alındı')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () use ($record) {
                        $record->update(['durum' => Order::DURUM_ONAYLANDI]);
                        Notification::make()->title('Üretici onayı tamamlandı, sipariş onaylandı.')->success()->send();
                    });
            }
            $actions[] = Action::make('seri_guncelle')
                ->label('Seri no güncelle')
                ->icon('heroicon-o-hashtag')
                ->color('gray')
                ->form([
                    TextInput::make('seri_no')->label('Mevcut seri (S)')->default($record->seri_no),
                    TextInput::make('yeni_seri_no')->label('Yeni seri')->default($record->yeni_seri_no),
                    DatePicker::make('yeni_seri_tarihi')->label('Tarih')->default($record->yeni_seri_tarihi),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'seri_no' => $data['seri_no'] ?? null,
                        'yeni_seri_no' => $data['yeni_seri_no'] ?? null,
                        'yeni_seri_tarihi' => $data['yeni_seri_tarihi'] ?? null,
                    ]);
                    Notification::make()->title('Seri bilgileri güncellendi.')->success()->send();
                });
            $actions[] = Action::make('imalat_listesi')
                ->label('İmalat listesi oluşturuldu')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->visible(fn () => $record->durum === Order::DURUM_ONAYLANDI || $record->durum === Order::DURUM_IMALATTA)
                ->disabled(fn () => $record->imalat_listesi_cikti_at !== null)
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $record->update(['imalat_listesi_cikti_at' => now()]);
                    Notification::make()->title('İmalat listesi zaman damgası kaydedildi.')->success()->send();
                });
            $actions[] = Action::make('export_excel')
                ->label('Excel İndir')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('admin.orders.export-excel', ['id' => $record->id], absolute: true))
                ->openUrlInNewTab();
            $actions[] = Action::make('export_csv')
                ->label('CSV İndir')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(route('admin.orders.export-csv', ['id' => $record->id], absolute: true))
                ->openUrlInNewTab();
            $actions[] = Action::make('export_pdf')
                ->label('PDF İndir')
                ->icon('heroicon-o-document')
                ->color('gray')
                ->url(route('admin.orders.export-pdf', ['id' => $record->id], absolute: true))
                ->openUrlInNewTab();
            $actions[] = EditAction::make();
        }

        return $actions;
    }
}

<?php

namespace App\Filament\Musteri\Resources\OrderResource\Pages;

use App\Filament\Musteri\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $order = $this->getRecord();
        $tenant = \Filament\Facades\Filament::getTenant();
        if ($tenant && $order->dealer_id !== $tenant->getKey()) {
            abort(404);
        }
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        if ($record->durum !== Order::DURUM_MUSTERI_ONAYI_BEKLIYOR) {
            return [];
        }

        return [
            Action::make('onayla')
                ->label('Siparişi onayla')
                ->color('success')
                ->form([
                    Checkbox::make('kvkk')->label('Aydınlatma metni ve siparişimi onaylıyorum')->required()->accepted(),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'durum' => Order::DURUM_YONETIM_ONAYI_BEKLIYOR,
                        'kvkk_onay' => true,
                    ]);
                    Notification::make()->title('Sipariş onaylandı. Yönetim onayı bekleniyor.')->success()->send();
                }),
            Action::make('reddet')
                ->label('Geri gönder')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('aciklama')->label('Not')->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $not = (string) ($data['aciklama'] ?? '');
                    $onceki = $record->aciklama ? (string) $record->aciklama : '';
                    $record->update([
                        'durum' => Order::DURUM_TASLAK,
                        'aciklama' => trim($onceki.($onceki !== '' ? "\n\n" : '').'[Müşteri geri gönderdi] '.$not),
                    ]);
                    Notification::make()->title('Sipariş bayi taslağına alındı.')->warning()->send();
                }),
        ];
    }
}

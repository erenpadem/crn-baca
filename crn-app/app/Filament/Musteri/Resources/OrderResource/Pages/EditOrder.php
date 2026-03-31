<?php

namespace App\Filament\Musteri\Resources\OrderResource\Pages;

use App\Filament\Musteri\Resources\OrderResource;
use App\Filament\Musteri\Resources\OrderResource\Concerns\SyncsBayiLineListPrices;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditOrder extends EditRecord
{
    use SyncsBayiLineListPrices;

    protected static string $resource = OrderResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        if ($this->getRecord()->durum !== Order::DURUM_TASLAK) {
            abort(403);
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Teklif talebini düzenle';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['items'] = $this->syncBayiItemsListPrices($data['items'] ?? null);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn () => $this->getRecord()->durum === Order::DURUM_TASLAK),
        ];
    }
}

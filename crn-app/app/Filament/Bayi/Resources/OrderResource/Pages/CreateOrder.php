<?php

namespace App\Filament\Bayi\Resources\OrderResource\Pages;

use App\Filament\Bayi\Resources\OrderResource;
use App\Filament\Bayi\Resources\OrderResource\Concerns\SyncsBayiLineListPrices;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    use SyncsBayiLineListPrices;

    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['durum'] = Order::DURUM_TASLAK;
        if (auth()->id()) {
            $data['created_by'] = auth()->id();
        }
        $data['items'] = $this->syncBayiItemsListPrices($data['items'] ?? null);

        return $data;
    }
}

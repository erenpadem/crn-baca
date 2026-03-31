<?php

namespace App\Filament\Musteri\Resources\OrderResource\Pages;

use App\Filament\Musteri\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->hasRole('bayi') ?? false),
        ];
    }

    public function getTabs(): array
    {
        return [
            'bekleyen' => Tab::make('Süreçte / bekleyen')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('durum', Order::bayiBekleyenDurumlari())),
            'onayli' => Tab::make('Onaylı')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('durum', Order::bayiOnayliDurumlari())),
        ];
    }
}

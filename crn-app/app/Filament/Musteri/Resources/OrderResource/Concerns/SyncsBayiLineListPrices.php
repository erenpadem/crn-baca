<?php

namespace App\Filament\Musteri\Resources\OrderResource\Concerns;

use App\Models\Product;

trait SyncsBayiLineListPrices
{
    /**
     * @param  array<int, array<string, mixed>>|null  $items
     * @return array<int, array<string, mixed>>|null
     */
    protected function syncBayiItemsListPrices(?array $items): ?array
    {
        if (! is_array($items)) {
            return $items;
        }
        foreach ($items as $i => $item) {
            if (empty($item['product_id'])) {
                continue;
            }
            $needsPrice = ! isset($item['birim_fiyat'])
                || $item['birim_fiyat'] === null
                || $item['birim_fiyat'] === ''
                || (float) $item['birim_fiyat'] <= 0;
            if ($needsPrice) {
                $p = Product::query()->find($item['product_id']);
                if ($p) {
                    $items[$i]['birim_fiyat'] = $p->fiyat_liste;
                }
            }
        }

        return $items;
    }
}

<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->flattenFormDataToFillable($data);
    }

    /**
     * Form verisi iç içe gelebilir; düzleştirip sadece Product fillable alanlarını döndürür.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function flattenFormDataToFillable(array $data): array
    {
        $fillable = (new Product)->getFillable();
        // Tek anahtar altında sarılı state (örn. mountedFormData)
        if (count($data) === 1 && is_array($first = reset($data))) {
            $data = $first;
        }
        $flat = Arr::dot($data);
        $result = [];
        foreach ($flat as $key => $value) {
            $attr = str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key;
            if (in_array($attr, $fillable, true)) {
                $result[$attr] = $value;
            }
        }
        return $result;
    }
}

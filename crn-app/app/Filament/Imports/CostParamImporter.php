<?php

namespace App\Filament\Imports;

use App\Models\CostParam;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

class CostParamImporter extends Importer
{
    protected static ?string $model = CostParam::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Görünen ad')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['name', 'gorunen_ad', 'Görünen Ad', 'Görünen ad', 'NAME']),
            ImportColumn::make('key')
                ->label('Parametre kodu (key)')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['key', 'kod', 'parametre_kodu', 'Parametre kodu', 'KEY']),
            ImportColumn::make('value')
                ->label('Değer')
                ->numeric()
                ->requiredMapping()
                ->rules(['required', 'numeric'])
                ->guess(['value', 'deger', 'Değer', 'VALUE']),
            ImportColumn::make('unit')
                ->label('Birim')
                ->ignoreBlankState()
                ->guess(['unit', 'birim', 'Birim', 'UNIT']),
        ];
    }

    public function resolveRecord(): ?CostParam
    {
        $key = isset($this->data['key']) ? trim((string) $this->data['key']) : '';
        if ($key === '') {
            return new CostParam;
        }

        return CostParam::firstOrNew(['key' => $key]);
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        if ($record instanceof CostParam && filled($record->key)) {
            Cache::forget('cost_param_'.$record->key);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Maliyet parametresi içe aktarımı tamamlandı. '.Number::format($import->successful_rows).' satır işlendi.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' satır hata verdi.';
        }

        return $body;
    }
}

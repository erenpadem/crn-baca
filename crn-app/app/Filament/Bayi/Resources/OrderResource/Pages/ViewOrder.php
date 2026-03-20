<?php

namespace App\Filament\Bayi\Resources\OrderResource\Pages;

use App\Filament\Bayi\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\NumberFormat;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
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

        if ($record->durum === Order::DURUM_TASLAK) {
            $actions[] = EditAction::make();
            $actions[] = Action::make('teklif_olarak_gonder')
                ->label('Teklif olarak gönder')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Satışa teklif gönder')
                ->modalDescription('Talebiniz yönetim panelinde değerlendirilmek üzere iletilecektir. Fiyat ve maliyet bilgileri yalnızca satış ekibinde görünür.')
                ->action(function () use ($record) {
                    $record->update(['durum' => Order::DURUM_YONETIM_ONAYI_BEKLIYOR]);
                    Notification::make()->title('Teklif talebi satışa iletildi.')->success()->send();
                });
        }

        if (Order::bayiKarsiTeklifEdebilir($record)) {
            $actions[] = Action::make('karsi_teklif')
                ->label($record->bayi_karsi_gonderim_at ? 'Karşı teklifi güncelle' : 'Karşı teklif ver')
                ->color('warning')
                ->icon('heroicon-o-pencil-square')
                ->modalHeading('İskonto ve fiyat teklifiniz')
                ->modalDescription('Satışın paylaştığı fiyatlara göre istediğiniz iskonto veya kalem bazında önerdiğiniz birim fiyatları girin.')
                ->modalWidth('7xl')
                ->form([
                    TextInput::make('bayi_karsi_iskonto_yuzde')
                        ->label('Karşı teklif iskonto %')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->formatStateUsing(fn ($s) => $s !== null && $s !== '' ? NumberFormat::formatForInput((float) $s, 2) : null)
                        ->dehydrateStateUsing(fn ($s) => NumberFormat::parseInput($s))
                        ->default($record->bayi_karsi_iskonto_yuzde),
                    Repeater::make('karsi_kalemler')
                        ->label('Kalem bazlı teklif birim fiyatı')
                        ->schema([
                            Hidden::make('order_item_id'),
                            TextInput::make('urun')->label('Ürün')->disabled()->dehydrated(false),
                            TextInput::make('satis_birim_fiyat')->label('Satış birim fiyatı')->disabled()->dehydrated(false),
                            TextInput::make('bayi_karsi_birim_fiyat')
                                ->label('Önerdiğiniz birim fiyat (₺)')
                                ->numeric()
                                ->step(0.0001)
                                ->formatStateUsing(fn ($s) => $s !== null && $s !== '' ? NumberFormat::formatForInput((float) $s, 4) : null)
                                ->dehydrateStateUsing(fn ($s) => NumberFormat::parseInput($s)),
                        ])
                        ->columns(4)
                        ->default(function () use ($record) {
                            $record->loadMissing('items.product');

                            return $record->items->map(fn (OrderItem $i) => [
                                'order_item_id' => $i->id,
                                'urun' => $i->product?->malzeme_aciklamasi ?? '—',
                                'satis_birim_fiyat' => NumberFormat::formatForInput((float) $i->birim_fiyat, 4),
                                'bayi_karsi_birim_fiyat' => $i->bayi_karsi_birim_fiyat !== null
                                    ? NumberFormat::formatForInput((float) $i->bayi_karsi_birim_fiyat, 4)
                                    : null,
                            ])->values()->all();
                        })
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                    Textarea::make('bayi_karsi_not')
                        ->label('Açıklama / not')
                        ->rows(3)
                        ->default($record->bayi_karsi_not),
                ])
                ->action(function (array $data) use ($record) {
                    foreach ($data['karsi_kalemler'] ?? [] as $row) {
                        $id = $row['order_item_id'] ?? null;
                        if (! $id) {
                            continue;
                        }
                        $item = OrderItem::query()
                            ->whereKey($id)
                            ->where('order_id', $record->id)
                            ->first();
                        if (! $item) {
                            continue;
                        }
                        $item->update([
                            'bayi_karsi_birim_fiyat' => isset($row['bayi_karsi_birim_fiyat'])
                                ? NumberFormat::parseInput($row['bayi_karsi_birim_fiyat'])
                                : null,
                        ]);
                    }
                    $record->update([
                        'bayi_karsi_iskonto_yuzde' => NumberFormat::parseInput($data['bayi_karsi_iskonto_yuzde'] ?? null),
                        'bayi_karsi_not' => $data['bayi_karsi_not'] ?? null,
                        'bayi_karsi_gonderim_at' => now(),
                        'durum' => Order::DURUM_BAYI_KARSI_TEKLIF_VERDI,
                    ]);
                    $this->record = $record->fresh(['items.product']);
                    Notification::make()->title('Karşı teklifiniz kaydedildi.')->success()->send();
                });
        }

        return $actions;
    }
}

<?php

namespace App\Filament\Musteri\Resources\QuoteResource\Pages;

use App\Filament\Musteri\Resources\QuoteResource;
use App\Models\Quote;
use App\Support\NumberFormat;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $quote = $this->getRecord();
        $tenant = \Filament\Facades\Filament::getTenant();
        if ($tenant && $quote->dealer_id !== $tenant->getKey()) {
            abort(404);
        }
        if ($quote->durum === 'taslak') {
            abort(404);
        }
        $this->record->loadMissing(['items.product']);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 1,
            ])
            ->record($this->getRecord())
            ->components([
                Section::make('Teklif Bilgileri')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('teklif_no')->label('Teklif No'),
                        TextEntry::make('durum')
                            ->label('Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ((string) $state) {
                                'taslak' => 'Taslak',
                                'gonderildi' => 'Gönderildi',
                                'musteri_teklif_verdi' => 'Karşı teklif verildi',
                                'onaylandi' => 'Onaylandı',
                                'reddedildi' => 'Reddedildi',
                                default => filled($state) ? (string) $state : '—',
                            })
                            ->color(fn (?string $state): string => match ((string) $state) {
                                'gonderildi' => 'info',
                                'musteri_teklif_verdi' => 'warning',
                                'onaylandi' => 'success',
                                'reddedildi' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('dealer.unvan')->label('Müşteri'),
                        TextEntry::make('musteri_iskonto_yuzde')
                            ->label('İstenilen iskonto %')
                            ->getStateUsing(function (Component $c): string {
                                $record = $c->getContainer()->getRecord();
                                if (! $record instanceof Quote || $record->musteri_iskonto_yuzde === null || $record->musteri_iskonto_yuzde === '') {
                                    return '—';
                                }

                                return number_format((float) $record->musteri_iskonto_yuzde, 2, ',', '.');
                            }),
                        TextEntry::make('musteri_net_tutar')
                            ->label('Net tutar (KDV hariç)')
                            ->getStateUsing(function (Component $c): string {
                                $record = $c->getContainer()->getRecord();
                                if (! $record instanceof Quote || $record->musteri_net_tutar === null || $record->musteri_net_tutar === '') {
                                    return '—';
                                }

                                return Number::currency((float) $record->musteri_net_tutar, 'TRY', config('app.locale'));
                            }),
                        TextEntry::make('musteri_not')->label('Not / açıklama')->placeholder('—')->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
                Section::make('Kalemler')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('_kalem_yok')
                            ->hiddenLabel()
                            ->getStateUsing(fn () => 'Bu teklifte ürün satırı tanımlı değil.')
                            ->visible(fn (): bool => $this->getRecord()->items->isEmpty()),
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product.malzeme_aciklamasi')->label('Ürün'),
                                TextEntry::make('birim_fiyat')->label('Teklif birim')->money('TRY'),
                                TextEntry::make('musteri_maliyet_birim')->label('İstenilen maliyet birim fiyatı (₺)')->money('TRY')->placeholder('—'),
                                TextEntry::make('musteri_birim_fiyat')->label('İstenilen satış birim fiyatı (₺)')->money('TRY')->placeholder('—'),
                                TextEntry::make('adet')->label('Adet'),
                                TextEntry::make('tutar')->label('Tutar')->money('TRY'),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 3,
                                'lg' => 4,
                                'xl' => 6,
                            ])
                            ->visible(fn (): bool => $this->getRecord()->items->isNotEmpty()),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        if ($record->durum !== 'gonderildi') {
            return [];
        }

        return [
            Action::make('onayla')
                ->label('Onayla')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Teklifi onaylıyor musunuz?')
                ->fillForm(fn (): array => $this->quoteYanitFormBaslangic())
                ->form([
                    $this->iskontoFormField(),
                    $this->netTutarFormField(),
                    Textarea::make('musteri_not')->label('Not')->rows(2),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'durum' => Quote::DURUM_ONAYLANDI,
                        'musteri_iskonto_yuzde' => NumberFormat::parseInput($data['musteri_iskonto_yuzde'] ?? null),
                        'musteri_net_tutar' => NumberFormat::parseInput($data['musteri_net_tutar'] ?? null),
                        'musteri_not' => $data['musteri_not'] ?? null,
                        'musteri_yanit_tarihi' => now(),
                    ]);
                    $this->record = $record->fresh(['items.product']);
                    Notification::make()->title('Teklif onaylandı.')->success()->send();
                }),
            Action::make('teklif_ver')
                ->label('Karşı Teklif Ver')
                ->color('warning')
                ->icon('heroicon-o-pencil-square')
                ->modalHeading('Karşı teklifinizi girin')
                ->modalDescription('İskonto, net tutar ve isteğe bağlı kalem detayı ile karşı teklif oluşturabilirsiniz.')
                ->modalWidth('7xl')
                ->fillForm(fn (): array => $this->karsiTeklifFormBaslangic())
                ->form(function (): array {
                    $record = $this->getRecord();
                    $hasItems = $record->items()->exists();

                    $rows = [
                        $this->iskontoFormField(),
                        $this->netTutarFormField(),
                    ];

                    if ($hasItems) {
                        $rows[] = Repeater::make('teklif_ver_items')
                            ->label('Kalemler')
                            ->schema([
                                Hidden::make('quote_item_id'),
                                TextInput::make('product_name')->label('Ürün')->disabled()->dehydrated(false),
                                TextInput::make('birim_fiyat')->label('Teklif birim fiyatı')->disabled()->dehydrated(false),
                                TextInput::make('adet')->label('Adet')->disabled()->dehydrated(false),
                                TextInput::make('musteri_maliyet_birim')
                                    ->label('İstenilen maliyet birim fiyatı (₺)')
                                    ->helperText('Ürün başına önerdiğiniz maliyet / taban tutarı.')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? NumberFormat::formatForInput((float) $state, 4) : null)
                                    ->dehydrateStateUsing(fn ($s) => NumberFormat::parseInput($s)),
                                TextInput::make('musteri_birim_fiyat')
                                    ->label('İstenilen satış birim fiyatı (₺)')
                                    ->helperText('Ürün başına önerdiğiniz satış fiyatı. Boş bırakırsanız satır tutarı maliyet birim fiyatından hesaplanır (o da yoksa teklifteki birim fiyattan).')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? NumberFormat::formatForInput((float) $state, 4) : null)
                                    ->dehydrateStateUsing(fn ($s) => NumberFormat::parseInput($s)),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 3,
                                'lg' => 4,
                                'xl' => 6,
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false);
                    } else {
                        $rows[] = Placeholder::make('kalem_yok_bilgi')
                            ->label('Kalemler')
                            ->content('Bu teklifte ürün satırı tanımlı değil. Net tutar, iskonto ve not ile karşı teklif verebilirsiniz.');
                    }

                    $rows[] = Textarea::make('musteri_not')
                        ->label('Açıklama / Not')
                        ->rows(3);

                    return $rows;
                })
                ->action(function (array $data) use ($record) {
                    $record->loadMissing('items');
                    foreach ($data['teklif_ver_items'] ?? [] as $row) {
                        $item = $record->items->firstWhere('id', $row['quote_item_id'] ?? null);
                        if (! $item) {
                            continue;
                        }
                        $item->update([
                            'musteri_birim_fiyat' => NumberFormat::parseInput($row['musteri_birim_fiyat'] ?? null),
                            'musteri_maliyet_birim' => NumberFormat::parseInput($row['musteri_maliyet_birim'] ?? null),
                        ]);
                    }
                    $record->update([
                        'durum' => Quote::DURUM_MUSTERI_TEKLIF_VERDI,
                        'musteri_iskonto_yuzde' => NumberFormat::parseInput($data['musteri_iskonto_yuzde'] ?? null),
                        'musteri_net_tutar' => NumberFormat::parseInput($data['musteri_net_tutar'] ?? null),
                        'musteri_not' => $data['musteri_not'] ?? null,
                        'musteri_yanit_tarihi' => now(),
                    ]);
                    $this->record = $record->fresh(['items.product']);
                    Notification::make()->title('Karşı teklifiniz gönderildi.')->success()->send();
                }),
            Action::make('reddet')
                ->label('Reddet')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('musteri_not')->label('Red nedeni')->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'durum' => Quote::DURUM_REDDEDILDI,
                        'musteri_not' => $data['musteri_not'],
                        'musteri_yanit_tarihi' => now(),
                    ]);
                    $this->record = $record->fresh(['items.product']);
                    Notification::make()->title('Teklif reddedildi.')->warning()->send();
                }),
        ];
    }

    private function iskontoFormField(): TextInput
    {
        return TextInput::make('musteri_iskonto_yuzde')
            ->label('İstenilen iskonto %')
            ->helperText('Tüm teklif için talep ettiğiniz iskonto oranı.')
            ->numeric()
            ->minValue(0)
            ->maxValue(100)
            ->step(0.01)
            ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? NumberFormat::formatForInput((float) $state, 2) : null)
            ->dehydrateStateUsing(fn ($s) => NumberFormat::parseInput($s));
    }

    private function netTutarFormField(): TextInput
    {
        return TextInput::make('musteri_net_tutar')
            ->label('Net tutar (₺, KDV hariç)')
            ->helperText('Toplam net tutarı doğrudan girmek için.')
            ->numeric()
            ->step(0.01)
            ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? NumberFormat::formatForInput((float) $state, 2) : null)
            ->dehydrateStateUsing(fn ($s) => NumberFormat::parseInput($s));
    }

    /**
     * @return array<string, mixed>
     */
    private function quoteYanitFormBaslangic(): array
    {
        $record = $this->getRecord()->fresh(['items.product']);

        return [
            'musteri_iskonto_yuzde' => $record->musteri_iskonto_yuzde,
            'musteri_net_tutar' => $record->musteri_net_tutar !== null
                ? NumberFormat::formatForInput((float) $record->musteri_net_tutar, 2)
                : null,
            'musteri_not' => $record->musteri_not,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function karsiTeklifFormBaslangic(): array
    {
        $record = $this->getRecord()->fresh(['items.product']);
        $state = $this->quoteYanitFormBaslangic();

        if ($record->items->isEmpty()) {
            return $state;
        }

        $state['teklif_ver_items'] = $record->items->map(fn ($i) => [
            'quote_item_id' => $i->id,
            'product_name' => $i->product?->malzeme_aciklamasi ?? '—',
            'birim_fiyat' => NumberFormat::formatForInput((float) $i->birim_fiyat, 4),
            'adet' => NumberFormat::formatForInput((float) $i->adet, 4),
            'musteri_maliyet_birim' => $i->musteri_maliyet_birim !== null
                ? NumberFormat::formatForInput((float) $i->musteri_maliyet_birim, 4)
                : null,
            'musteri_birim_fiyat' => $i->musteri_birim_fiyat !== null
                ? NumberFormat::formatForInput((float) $i->musteri_birim_fiyat, 4)
                : NumberFormat::formatForInput((float) $i->birim_fiyat, 4),
        ])->values()->all();

        return $state;
    }
}

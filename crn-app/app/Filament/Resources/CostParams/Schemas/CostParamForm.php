<?php

namespace App\Filament\Resources\CostParams\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostParamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 1,
            ])
            ->components([
                Section::make('Maliyet Parametresi')
                    ->description('Excel “genel hesap” sayfasındaki gibi maliyet/fiyat formüllerinde kullanılan sabitler. Örn: sac fiyatı (kg), maşon fiyatı, KDV oranı. Kodda CostParam::getByKey("parametre_kodu") ile okunur.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')->label('Görünen ad')->required()->maxLength(255)->placeholder('Örn: SAC FİYATI')->helperText('Listede ve raporlarda görünen isim.'),
                        TextInput::make('key')->label('Parametre kodu')->required()->maxLength(100)->unique(ignoreRecord: true)->placeholder('Örn: sac_fiyati')->helperText('Kodda ve formüllerde kullanılan benzersiz kod. Sadece küçük harf, rakam ve alt çizgi. Kayıt sonrası değiştirmeyin.'),
                        self::decimalInput('value', 'Değer', 0)->required()->helperText('Sayısal değer (örn: 6,5 veya 18).'),
                        TextInput::make('unit')->label('Birim')->maxLength(20)->placeholder('Örn: TL/kg, %')->helperText('İsteğe bağlı; değerin birimi.'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ]);
    }

    protected static function decimalInput(string $name, string $label, $default = null): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->inputMode('decimal')
            ->step(0.0001)
            ->default($default)
            ->nullable()
            ->dehydrated(fn ($state) => true)
            ->dehydrateStateUsing(function ($state) use ($default) {
                if ($state === '' || $state === null) {
                    return $default;
                }
                $v = str_replace(',', '.', (string) $state);

                return is_numeric($v) ? (float) $v : $default;
            });
    }
}

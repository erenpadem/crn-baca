<?php

namespace App\Filament\Resources\Puantaj\Schemas;

use App\Models\Personel;
use App\Models\Puantaj;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PuantajForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Puantaj Bilgileri')
                    ->description('Personelin günlük devam durumunu kaydedin. Gelmedi seçildiğinde açıklama alanını doldurun.')
                    ->schema([
                        Select::make('personel_id')
                            ->label('Personel')
                            ->relationship(
                                'personel',
                                'ad_soyad',
                                fn ($q) => $q ? $q->where('aktif', true) : Personel::query()->where('aktif', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->noOptionsMessage('Personel bulunamadı. Önce Personel sayfasından personel ekleyin.'),
                        DatePicker::make('tarih')
                            ->label('Tarih')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Select::make('durum')
                            ->label('Durum')
                            ->options(Puantaj::durumlar())
                            ->required()
                            ->default(Puantaj::DURUM_TAM_GUN)
                            ->native(false),
                        Textarea::make('aciklama')
                            ->label('Açıklama (Neden gelmedi?)')
                            ->rows(2)
                            ->placeholder('Örn: Hastalık, izin, vb.')
                            ->helperText('Gelmedi seçildiğinde bu alanı doldurmanız önerilir.')
                            ->columnSpanFull(),
                        Textarea::make('notlar')->label('Notlar')->rows(2)->columnSpanFull(),
                        TimePicker::make('giris_saati')->label('Giriş Saati')->seconds(false),
                        TimePicker::make('cikis_saati')->label('Çıkış Saati')->seconds(false),
                    ])->columns(2),
            ]);
    }
}

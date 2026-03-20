<?php

namespace App\Filament\Bayi\Pages;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class CariHesabim extends Page
{
    protected static ?string $navigationLabel = 'Cari hesabım';

    protected static string|\UnitEnum|null $navigationGroup = 'Hesabım';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.bayi.pages.cari-hesabim';

    public function getHeading(): string
    {
        return 'Cari hesabım';
    }

    public function getDealer(): ?\App\Models\Dealer
    {
        $t = Filament::getTenant();

        return $t instanceof \App\Models\Dealer ? $t : null;
    }
}

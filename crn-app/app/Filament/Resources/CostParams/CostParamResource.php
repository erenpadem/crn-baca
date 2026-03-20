<?php

namespace App\Filament\Resources\CostParams;

use App\Filament\Resources\CostParams\Pages\CreateCostParam;
use App\Filament\Resources\CostParams\Pages\EditCostParam;
use App\Filament\Resources\CostParams\Pages\ListCostParams;
use App\Filament\Resources\CostParams\Pages\ViewCostParam;
use App\Filament\Resources\CostParams\Schemas\CostParamForm;
use App\Filament\Resources\CostParams\Schemas\CostParamInfolist;
use App\Filament\Resources\CostParams\Tables\CostParamsTable;
use App\Models\CostParam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CostParamResource extends Resource
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('imalathane') ? false : parent::canViewAny();
    }
    protected static ?string $model = CostParam::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Urun & Maliyet';

    protected static ?string $navigationLabel = 'Maliyet Parametreleri';

    protected static ?string $modelLabel = 'Maliyet Parametresi';

    protected static ?string $pluralModelLabel = 'Maliyet Parametreleri';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    public static function form(Schema $schema): Schema
    {
        return CostParamForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CostParamInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostParamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostParams::route('/'),
            'create' => CreateCostParam::route('/create'),
            'view' => ViewCostParam::route('/{record}'),
            'edit' => EditCostParam::route('/{record}/edit'),
        ];
    }
}

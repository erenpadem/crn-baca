<?php

namespace App\Filament\Resources\Puantaj;

use App\Filament\Resources\Puantaj\Pages\CreatePuantaj;
use App\Filament\Resources\Puantaj\Pages\EditPuantaj;
use App\Filament\Resources\Puantaj\Pages\ListPuantaj;
use App\Filament\Resources\Puantaj\Pages\ViewPuantaj;
use App\Filament\Resources\Puantaj\Schemas\PuantajForm;
use App\Filament\Resources\Puantaj\Schemas\PuantajInfolist;
use App\Filament\Resources\Puantaj\Tables\PuantajTable;
use App\Models\Puantaj;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PuantajResource extends Resource
{
    protected static ?string $model = Puantaj::class;

    protected static string|\UnitEnum|null $navigationGroup = 'İnsan Kaynakları';

    protected static ?string $navigationLabel = 'Puantaj';

    protected static ?string $modelLabel = 'Puantaj';

    protected static ?string $pluralModelLabel = 'Puantaj';

    protected static ?string $recordTitleAttribute = 'tarih';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function form(Schema $schema): Schema
    {
        return PuantajForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PuantajInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PuantajTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPuantaj::route('/'),
            'create' => CreatePuantaj::route('/create'),
            'view' => ViewPuantaj::route('/{record}'),
            'edit' => EditPuantaj::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Personel;

use App\Filament\Resources\Personel\Pages\CreatePersonel;
use App\Filament\Resources\Personel\Pages\EditPersonel;
use App\Filament\Resources\Personel\Pages\ListPersonel;
use App\Filament\Resources\Personel\Pages\ViewPersonel;
use App\Filament\Resources\Personel\Schemas\PersonelForm;
use App\Filament\Resources\Personel\Schemas\PersonelInfolist;
use App\Filament\Resources\Personel\Tables\PersonelTable;
use App\Models\Personel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PersonelResource extends Resource
{
    protected static ?string $model = Personel::class;

    protected static string|\UnitEnum|null $navigationGroup = 'İnsan Kaynakları';

    protected static ?string $navigationLabel = 'Personel İletişim';

    protected static ?string $modelLabel = 'Personel';

    protected static ?string $pluralModelLabel = 'Personel';

    protected static ?string $recordTitleAttribute = 'ad_soyad';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return PersonelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PersonelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonelTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PuantajRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPersonel::route('/'),
            'create' => CreatePersonel::route('/create'),
            'view' => ViewPersonel::route('/{record}'),
            'edit' => EditPersonel::route('/{record}/edit'),
        ];
    }
}

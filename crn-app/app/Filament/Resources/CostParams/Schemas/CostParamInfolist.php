<?php

namespace App\Filament\Resources\CostParams\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class CostParamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Görünen ad')->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->name),
                TextEntry::make('key')->label('Parametre kodu')->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->key),
                self::decimalEntry('value', 'Değer'),
                TextEntry::make('unit')->label('Birim')->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->unit)->placeholder('–'),
            ]);
    }

    protected static function decimalEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->getConstantStateUsing(fn (Component $c) => $c->getContainer()->getRecord()?->getAttribute($name))
            ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 4, ',', '.') : '–')
            ->placeholder('–');
    }
}

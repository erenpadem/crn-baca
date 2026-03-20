<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Ad'),
                TextEntry::make('email')->label('E-posta'),
                TextEntry::make('email_verified_at')
                    ->label('E-posta doğrulama tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('-'),
                TextEntry::make('dealer.unvan')
                    ->label('Bayi')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Oluşturulma tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Güncellenme tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('-'),
            ]);
    }
}

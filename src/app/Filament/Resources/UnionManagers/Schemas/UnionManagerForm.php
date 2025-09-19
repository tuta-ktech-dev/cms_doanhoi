<?php

namespace App\Filament\Resources\UnionManagers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnionManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('union_id')
                    ->required()
                    ->numeric(),
                TextInput::make('position')
                    ->default(null),
            ]);
    }
}

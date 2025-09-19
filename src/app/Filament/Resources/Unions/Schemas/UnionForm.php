<?php

namespace App\Filament\Resources\Unions\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UnionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('logo')
                    ->image()
                    ->imageEditor()
                    ->directory('logos')
                    ->visibility('public')
                    ->maxSize(2048) // 2MB
                    ->imageResizeMode('contain')
                    ->imageCropAspectRatio('16:9'),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                    ->default('active')
                    ->required(),
            ]);
    }
}

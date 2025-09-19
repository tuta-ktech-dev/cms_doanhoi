<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('student_id')
                    ->required(),
                DatePicker::make('date_of_birth'),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
                    ->default(null),
                TextInput::make('faculty')
                    ->default(null),
                TextInput::make('class')
                    ->default(null),
                TextInput::make('course')
                    ->default(null),
                TextInput::make('activity_points')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}

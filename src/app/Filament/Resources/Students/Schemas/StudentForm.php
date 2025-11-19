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
                Select::make('course')
                    ->label('Khóa')
                    ->options([
                        '1' => 'Khóa 1',
                        '2' => 'Khóa 2',
                        '3' => 'Khóa 3',
                        '4' => 'Khóa 4',
                        '5' => 'Khóa 5',
                        '6' => 'Khóa 6',
                        '7' => 'Khóa 7',
                        '8' => 'Khóa 8',
                        '9' => 'Khóa 9',
                        '10' => 'Khóa 10',
                        '11' => 'Khóa 11',
                        '12' => 'Khóa 12',
                        '13' => 'Khóa 13',
                        '14' => 'Khóa 14',
                    ])
                    ->default(null),
                TextInput::make('activity_points')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}

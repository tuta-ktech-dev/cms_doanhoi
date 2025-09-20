<?php

namespace App\Filament\Resources\EventAttendances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EventAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('event_id')
                    ->label('Sự kiện')
                    ->relationship('event', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('user_id')
                    ->label('Người tham gia')
                    ->relationship('user', 'full_name')
                    ->required()
                    ->searchable()
                    ->preload(),

                DateTimePicker::make('attended_at')
                    ->label('Thời gian điểm danh')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y H:i'),

                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'present' => 'Có mặt',
                        'absent' => 'Vắng mặt',
                        'late' => 'Đi muộn',
                        'excused' => 'Có phép',
                    ])
                    ->required()
                    ->default('present'),

                Textarea::make('notes')
                    ->label('Ghi chú')
                    ->maxLength(500)
                    ->rows(3)
                    ->placeholder('Ghi chú về việc điểm danh...'),
            ]);
    }
}
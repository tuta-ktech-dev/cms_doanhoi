<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use App\Models\Event;
use App\Models\User;
use App\Enums\RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Sự kiện')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->isAdmin()) {
                            return Event::all()->pluck('title', 'id');
                        }
                        if ($user->isUnionManager()) {
                            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
                            return Event::whereIn('union_id', $userUnionIds)->pluck('title', 'id');
                        }
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                
                Select::make('user_id')
                    ->label('Sinh viên')
                    ->options(function () {
                        return User::where('role', RoleEnum::STUDENT->value)
                            ->pluck('full_name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                
                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                    ])
                    ->required()
                    ->default('pending'),
                
                Textarea::make('notes')
                    ->label('Ghi chú')
                    ->rows(3)
                    ->maxLength(500),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\Pages\CreateUser;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('full_name')
                    ->default(null),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                FileUpload::make('avatar')
                    ->image()
                    ->imageEditor()
                    ->directory('avatars')
                    ->visibility('public')
                    ->maxSize(2048) // 2MB
                    ->circleCropper(),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'union_manager' => 'Union manager', 'student' => 'Student'])
                    ->default('student')
                    ->required(),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'banned' => 'Banned'])
                    ->default('active')
                    ->required(),
                DateTimePicker::make('last_login'),
            ]);
    }
}

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
                    ->label('Tên đăng nhập')
                    ->required(),
                TextInput::make('email')
                    ->label('Địa chỉ email')
                    ->email()
                    ->required(),
                TextInput::make('full_name')
                    ->label('Họ và tên')
                    ->default(null),
                TextInput::make('password')
                    ->label('Mật khẩu')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null),
                TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->tel()
                    ->default(null),
                FileUpload::make('avatar')
                    ->label('Ảnh đại diện')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->maxSize(2048) // 2MB
                    ->circleCropper(),
                Select::make('role')
                    ->label('Vai trò')
                    ->options([
                        'admin' => 'Quản trị',
                        'union_manager' => 'Quản lý đoàn hội',
                        'student' => 'Sinh viên',
                    ])
                    ->default('student')
                    ->required(),
                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Hoạt động',
                        'inactive' => 'Tạm ngưng',
                        'banned' => 'Đã khóa',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users;

use App\Enums\PermissionEnum;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Người dùng';

    protected static ?string $modelLabel = 'Người dùng';

    protected static ?string $pluralModelLabel = 'Người dùng';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý người dùng';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::VIEW_USERS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::CREATE_USERS->value) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::EDIT_USERS->value) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::DELETE_USERS->value) ?? false;
    }
}

<?php

namespace App\Filament\Resources\Unions;

use App\Enums\PermissionEnum;
use App\Filament\Resources\Unions\Pages\CreateUnion;
use App\Filament\Resources\Unions\Pages\EditUnion;
use App\Filament\Resources\Unions\Pages\ListUnions;
use App\Filament\Resources\Unions\RelationManagers\ManagersRelationManager;
use App\Filament\Resources\Unions\Schemas\UnionForm;
use App\Filament\Resources\Unions\Tables\UnionsTable;
use App\Models\Union;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnionResource extends Resource
{
    protected static ?string $model = Union::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý tổ chức';
    }

    public static function form(Schema $schema): Schema
    {
        return UnionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ManagersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnions::route('/'),
            'create' => CreateUnion::route('/create'),
            'edit' => EditUnion::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::VIEW_UNIONS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::CREATE_UNIONS->value) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::EDIT_UNIONS->value) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::DELETE_UNIONS->value) ?? false;
    }
}

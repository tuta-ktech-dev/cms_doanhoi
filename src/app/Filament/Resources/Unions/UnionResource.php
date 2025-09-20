<?php

namespace App\Filament\Resources\Unions;

use App\Enums\PermissionEnum;
use App\Filament\Resources\Unions\Pages\ListUnions;
use App\Filament\Resources\Unions\Pages\ViewUnion;
use App\Filament\Resources\Unions\Tables\UnionsTable;
use App\Models\Union;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class UnionResource extends Resource
{
    protected static ?string $model = Union::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    
    protected static ?string $navigationLabel = 'Đoàn hội';
    
    protected static ?string $modelLabel = 'Đoàn hội';
    
    protected static ?string $pluralModelLabel = 'Quản lý đoàn hội';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý hệ thống';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return UnionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnions::route('/'),
            'view' => ViewUnion::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
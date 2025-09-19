<?php

namespace App\Filament\Resources\UnionManagers;

use App\Filament\Resources\UnionManagers\Pages\CreateUnionManager;
use App\Filament\Resources\UnionManagers\Pages\EditUnionManager;
use App\Filament\Resources\UnionManagers\Pages\ListUnionManagers;
use App\Filament\Resources\UnionManagers\Schemas\UnionManagerForm;
use App\Filament\Resources\UnionManagers\Tables\UnionManagersTable;
use App\Models\UnionManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnionManagerResource extends Resource
{
    protected static ?string $model = UnionManager::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    // Ẩn resource này khỏi navigation
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return UnionManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnionManagersTable::configure($table);
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
            'index' => ListUnionManagers::route('/'),
            'create' => CreateUnionManager::route('/create'),
            'edit' => EditUnionManager::route('/{record}/edit'),
        ];
    }
}

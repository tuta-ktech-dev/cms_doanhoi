<?php

namespace App\Filament\Resources\EventAttendances;

use App\Enums\PermissionEnum;
use App\Filament\Resources\EventAttendances\Pages\CreateEventAttendance;
use App\Filament\Resources\EventAttendances\Pages\EditEventAttendance;
use App\Filament\Resources\EventAttendances\Pages\ListEventAttendances;
use App\Filament\Resources\EventAttendances\Schemas\EventAttendanceForm;
use App\Filament\Resources\EventAttendances\Tables\EventAttendancesTable;
use App\Models\EventAttendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventAttendanceResource extends Resource
{
    protected static ?string $model = EventAttendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    
    protected static ?string $navigationLabel = 'Điểm danh';
    
    protected static ?string $modelLabel = 'Điểm danh';
    
    protected static ?string $pluralModelLabel = 'Điểm danh sự kiện';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý sự kiện';
    }

    public static function getNavigationSort(): ?int
    {
        return 3; // Hiển thị sau Events (1) và Event Registrations (2)
    }

    public static function form(Schema $schema): Schema
    {
        return EventAttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventAttendancesTable::configure($table);
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
            'index' => ListEventAttendances::route('/'),
            'create' => CreateEventAttendance::route('/create'),
            'edit' => EditEventAttendance::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        // Admin có thể xem tất cả
        if ($user->isAdmin()) {
            return true;
        }
        
        // Union Manager có thể xem điểm danh của sự kiện mình quản lý
        if ($user->isUnionManager()) {
            return true;
        }
        
        return false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::CREATE_ATTENDANCE->value) ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission(PermissionEnum::EDIT_ATTENDANCE->value)) {
            return false;
        }

        // Union Manager chỉ có thể edit điểm danh của sự kiện đoàn hội mình quản lý
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->event->union_id, $userUnionIds);
        }

        return true;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission(PermissionEnum::DELETE_ATTENDANCE->value)) {
            return false;
        }

        // Union Manager chỉ có thể delete điểm danh của sự kiện đoàn hội mình quản lý
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->event->union_id, $userUnionIds);
        }

        return true;
    }
}

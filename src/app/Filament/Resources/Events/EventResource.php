<?php

namespace App\Filament\Resources\Events;

use App\Enums\PermissionEnum;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    
    protected static ?string $navigationLabel = 'Sự kiện';
    
    protected static ?string $modelLabel = 'Sự kiện';
    
    protected static ?string $pluralModelLabel = 'Sự kiện';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý sự kiện';
    }

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
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
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::VIEW_EVENTS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::CREATE_EVENTS->value) ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission(PermissionEnum::EDIT_EVENTS->value)) {
            return false;
        }

        // Union Manager chỉ có thể edit sự kiện của đoàn hội mình quản lý
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->union_id, $userUnionIds);
        }

        return true;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission(PermissionEnum::DELETE_EVENTS->value)) {
            return false;
        }

        // Union Manager chỉ có thể delete sự kiện của đoàn hội mình quản lý
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->union_id, $userUnionIds);
        }

        return true;
    }
}

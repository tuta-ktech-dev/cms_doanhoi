<?php

namespace App\Filament\Resources\EventRegistrations;

use App\Enums\PermissionEnum;
use App\Filament\Resources\EventRegistrations\Pages\CreateEventRegistration;
use App\Filament\Resources\EventRegistrations\Pages\EditEventRegistration;
use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Filament\Resources\EventRegistrations\Pages\ViewEventRegistration;
use App\Filament\Resources\EventRegistrations\Schemas\EventRegistrationForm;
use App\Filament\Resources\EventRegistrations\Tables\EventRegistrationsTable;
use App\Models\EventRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý sự kiện';
    }

    public static function getNavigationSort(): ?int
    {
        return 2; // Hiển thị sau Events
    }

    public static function form(Schema $schema): Schema
    {
        return EventRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventRegistrationsTable::configure($table);
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
            'index' => ListEventRegistrations::route('/'),
            'create' => CreateEventRegistration::route('/create'),
            'view' => ViewEventRegistration::route('/{record}'),
            'edit' => EditEventRegistration::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission(PermissionEnum::VIEW_REGISTRATIONS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Không cho phép tạo đăng ký từ admin panel
    }

    public static function canView($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Admin có thể xem tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Union Manager chỉ có thể xem đăng ký của sự kiện mình tạo
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->event->union_id, $userUnionIds);
        }

        return false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Admin có thể edit tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Union Manager chỉ có thể edit đăng ký của sự kiện mình tạo
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->event->union_id, $userUnionIds);
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        return false; // Không cho phép xóa đăng ký
    }
}

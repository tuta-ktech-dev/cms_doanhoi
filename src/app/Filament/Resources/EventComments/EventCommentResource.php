<?php

namespace App\Filament\Resources\EventComments;

use App\Filament\Resources\EventComments\Pages\ListEventComments;
use App\Filament\Resources\EventComments\Pages\ViewEventComment;
use App\Filament\Resources\EventComments\Tables\EventCommentsTable;
use App\Models\EventComment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventCommentResource extends Resource
{
    protected static ?string $model = EventComment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    
    protected static ?string $navigationLabel = 'Bình luận sự kiện';
    
    protected static ?string $modelLabel = 'Bình luận';
    
    protected static ?string $pluralModelLabel = 'Bình luận sự kiện';
    
    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý sự kiện';
    }

    public static function table(Table $table): Table
    {
        return EventCommentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventComments::route('/'),
            'view' => ViewEventComment::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        // Admin và Union Manager có thể xem bình luận
        $user = auth()->user();
        return $user?->isAdmin() || $user?->isUnionManager();
    }

    public static function canCreate(): bool
    {
        return false; // Bình luận được tạo từ frontend
    }

    public static function canView($record): bool
    {
        // Admin và Union Manager có thể xem bình luận
        $user = auth()->user();
        if (!$user) return false;

        if ($user->isAdmin()) {
            return true;
        }
        
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->event->union_id, $userUnionIds);
        }
        
        return false;
    }


    public static function canEdit($record): bool
    {
        // Admin có thể sửa tất cả, Union Manager chỉ sửa bình luận của sự kiện mình quản lý
        $user = auth()->user();
        if ($user?->isAdmin()) {
            return true;
        }
        
        if ($user?->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->event->union_id, $userUnionIds);
        }
        
        return false;
    }

    public static function canDelete($record): bool
    {
        // Admin có thể xóa tất cả, Union Manager chỉ xóa bình luận của sự kiện mình quản lý
        $user = auth()->user();
        if ($user?->isAdmin()) {
            return true;
        }
        
        if ($user?->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return in_array($record->event->union_id, $userUnionIds);
        }
        
        return false;
    }
}

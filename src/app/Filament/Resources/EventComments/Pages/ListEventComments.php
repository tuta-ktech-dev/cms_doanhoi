<?php

namespace App\Filament\Resources\EventComments\Pages;

use App\Filament\Resources\EventComments\EventCommentResource;
use Filament\Resources\Pages\ListRecords;

class ListEventComments extends ListRecords
{
    protected static string $resource = EventCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        $user = auth()->user();
        
        // Admin thấy tất cả bình luận
        if ($user->isAdmin()) {
            return parent::getTableQuery();
        }
        
        // Union Manager chỉ thấy bình luận của sự kiện thuộc đoàn hội mình quản lý
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return parent::getTableQuery()
                ->whereHas('event', function ($query) use ($userUnionIds) {
                    $query->whereIn('union_id', $userUnionIds);
                });
        }
        
        return parent::getTableQuery();
    }
}

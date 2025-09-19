<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin xem tất cả sự kiện
            return parent::getTableQuery();
        }
        
        if ($user->isUnionManager()) {
            // Union Manager chỉ xem sự kiện của đoàn hội mình quản lý
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return parent::getTableQuery()->whereIn('union_id', $userUnionIds);
        }
        
        return parent::getTableQuery();
    }
}

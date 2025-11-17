<?php

namespace App\Filament\Resources\EventRegistrations\Pages;

use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventRegistrations extends ListRecords
{
    protected static string $resource = EventRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm đăng ký')
                ->visible(false),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        $user = auth()->user();
        
        // Admin thấy tất cả đăng ký
        if ($user->isAdmin()) {
            return parent::getTableQuery();
        }
        
        // Union Manager chỉ thấy đăng ký của sự kiện thuộc đoàn hội mình quản lý
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

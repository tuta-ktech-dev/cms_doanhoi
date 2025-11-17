<?php

namespace App\Filament\Resources\UnionManagers\Pages;

use App\Filament\Resources\UnionManagers\UnionManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnionManagers extends ListRecords
{
    protected static string $resource = UnionManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm quản lý đoàn hội'),
        ];
    }
}

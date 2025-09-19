<?php

namespace App\Filament\Resources\UnionManagers\Pages;

use App\Filament\Resources\UnionManagers\UnionManagerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUnionManager extends EditRecord
{
    protected static string $resource = UnionManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

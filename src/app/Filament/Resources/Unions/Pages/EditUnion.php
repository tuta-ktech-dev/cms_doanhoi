<?php

namespace App\Filament\Resources\Unions\Pages;

use App\Filament\Resources\Unions\UnionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUnion extends EditRecord
{
    protected static string $resource = UnionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

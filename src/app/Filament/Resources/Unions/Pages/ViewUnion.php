<?php

namespace App\Filament\Resources\Unions\Pages;

use App\Filament\Resources\Unions\UnionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUnion extends ViewRecord
{
    protected static string $resource = UnionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\UnionDetailStatsWidget::class,
        ];
    }
}

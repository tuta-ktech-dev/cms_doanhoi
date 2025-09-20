<?php

namespace App\Filament\Resources\EventAttendances\Pages;

use App\Filament\Resources\EventAttendances\EventAttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventAttendance extends EditRecord
{
    protected static string $resource = EventAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

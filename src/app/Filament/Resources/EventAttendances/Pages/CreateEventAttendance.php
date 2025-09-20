<?php

namespace App\Filament\Resources\EventAttendances\Pages;

use App\Filament\Resources\EventAttendances\EventAttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventAttendance extends CreateRecord
{
    protected static string $resource = EventAttendanceResource::class;
}

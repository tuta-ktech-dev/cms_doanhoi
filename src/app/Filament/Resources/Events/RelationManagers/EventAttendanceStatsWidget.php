<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\EventAttendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventAttendanceStatsWidget extends BaseWidget
{
    public ?int $eventId = null;

    protected function getStats(): array
    {
        if (!$this->eventId) {
            return [];
        }

        $query = EventAttendance::where('event_id', $this->eventId);
        
        $totalAttendance = $query->count();
        $presentCount = (clone $query)->where('status', 'present')->count();
        $absentCount = (clone $query)->where('status', 'absent')->count();
        $lateCount = (clone $query)->where('status', 'late')->count();
        
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        return [
            Stat::make('Tổng điểm danh', $totalAttendance)
                ->description('Tổng số lượt điểm danh')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Có mặt', $presentCount)
                ->description($totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) . '%' : '0%')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Vắng mặt', $absentCount)
                ->description($totalAttendance > 0 ? round(($absentCount / $totalAttendance) * 100, 1) . '%' : '0%')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Đi muộn', $lateCount)
                ->description($totalAttendance > 0 ? round(($lateCount / $totalAttendance) * 100, 1) . '%' : '0%')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\EventAttendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Lấy query base dựa trên quyền hạn
        $query = EventAttendance::query();
        
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            $query->whereHas('event', function ($q) use ($userUnionIds) {
                $q->whereIn('union_id', $userUnionIds);
            });
        }
        
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
                ->description($totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) . '% tổng số' : '0%')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Vắng mặt', $absentCount)
                ->description($totalAttendance > 0 ? round(($absentCount / $totalAttendance) * 100, 1) . '% tổng số' : '0%')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Đi muộn', $lateCount)
                ->description($totalAttendance > 0 ? round(($lateCount / $totalAttendance) * 100, 1) . '% tổng số' : '0%')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Tỷ lệ tham gia', $attendanceRate . '%')
                ->description('Tỷ lệ có mặt + đi muộn')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger')),
        ];
    }
}
<?php

namespace App\Filament\Widgets;

use App\Models\EventAttendance;
use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardAttendanceWidget extends BaseWidget
{
    protected ?string $heading = 'Tổng quan Dashboard';

    protected function getStats(): array
    {
        $user = auth()->user();

        // Lấy query base dựa trên quyền hạn
        $eventQuery = Event::query();
        $attendanceQuery = EventAttendance::query();
        $registrationQuery = EventRegistration::query();

        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            $eventQuery->whereIn('union_id', $userUnionIds);
            $attendanceQuery->whereHas('event', function ($q) use ($userUnionIds) {
                $q->whereIn('union_id', $userUnionIds);
            });
            $registrationQuery->whereHas('event', function ($q) use ($userUnionIds) {
                $q->whereIn('union_id', $userUnionIds);
            });
        }

        $totalEvents = $eventQuery->count();
        $totalAttendance = $attendanceQuery->count();
        $totalRegistrations = $registrationQuery->where('status', 'approved')->count();

        $presentCount = (clone $attendanceQuery)->where('status', 'present')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        return [
            Stat::make('Tổng sự kiện', $totalEvents)
                ->description('Sự kiện đã tạo')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Tổng đăng ký', $totalRegistrations)
                ->description('Đăng ký đã duyệt')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('Tổng điểm danh', $totalAttendance)
                ->description('Lượt điểm danh')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),

            Stat::make('Tỷ lệ tham gia', $attendanceRate . '%')
                ->description('Có mặt / Tổng điểm danh')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger')),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EventStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Base query với quyền hạn
        $baseQuery = Event::query();
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            $baseQuery->whereIn('union_id', $userUnionIds);
        }

        // Tổng số sự kiện
        $totalEvents = $baseQuery->count();
        
        // Sự kiện đã kết thúc
        $completedEvents = (clone $baseQuery)->where('end_date', '<', now())->count();
        
        // Sự kiện đang diễn ra
        $ongoingEvents = (clone $baseQuery)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
        
        // Sự kiện sắp diễn ra
        $upcomingEvents = (clone $baseQuery)->where('start_date', '>', now())->count();

        // Thống kê đăng ký và tham gia
        $registrationStats = (clone $baseQuery)
            ->where('end_date', '<', now())
            ->withCount(['registrations as approved_registrations_count' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->withCount('attendance')
            ->get();

        $totalRegistrations = $registrationStats->sum('approved_registrations_count');
        $totalAttendance = $registrationStats->sum('attendance_count');
        $attendanceRate = $totalRegistrations > 0 ? round(($totalAttendance / $totalRegistrations) * 100, 1) : 0;

        return [
            Stat::make('Tổng sự kiện', $totalEvents)
                ->description('Tất cả sự kiện')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Đã kết thúc', $completedEvents)
                ->description('Sự kiện hoàn thành')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Đang diễn ra', $ongoingEvents)
                ->description('Sự kiện hiện tại')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('warning'),

            Stat::make('Sắp diễn ra', $upcomingEvents)
                ->description('Sự kiện tương lai')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Tổng đăng ký', number_format($totalRegistrations))
                ->description('Đăng ký đã duyệt')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),

            Stat::make('Tổng tham gia', number_format($totalAttendance))
                ->description('Sinh viên tham gia')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Tỷ lệ tham gia', $attendanceRate . '%')
                ->description('Tham gia / Đăng ký')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger')),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Union;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnionDetailStatsWidget extends BaseWidget
{
    public ?Union $record = null;

    protected function getStats(): array
    {
        // Lấy record từ parent page nếu có
        $union = $this->record;
        
        if (!$union) {
            return [];
        }

        // Thống kê sự kiện
        $totalEvents = $union->events()->count();
        $completedEvents = $union->events()->where('end_date', '<', now())->count();
        $ongoingEvents = $union->events()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
        $upcomingEvents = $union->events()->where('start_date', '>', now())->count();

        // Thống kê đăng ký
        $totalRegistrations = $union->eventRegistrations()->count();
        $approvedRegistrations = $union->eventRegistrations()->where('event_registrations.status', 'approved')->count();
        $pendingRegistrations = $union->eventRegistrations()->where('event_registrations.status', 'pending')->count();
        $rejectedRegistrations = $union->eventRegistrations()->where('event_registrations.status', 'rejected')->count();

        // Thống kê điểm danh
        $totalAttendance = $union->eventAttendances()->count();
        $presentAttendance = $union->eventAttendances()->where('event_attendance.status', 'present')->count();
        $absentAttendance = $union->eventAttendances()->where('event_attendance.status', 'absent')->count();
        $lateAttendance = $union->eventAttendances()->where('event_attendance.status', 'late')->count();
        $excusedAttendance = $union->eventAttendances()->where('event_attendance.status', 'excused')->count();

        // Tính tỷ lệ
        $approvalRate = $totalRegistrations > 0 ? round(($approvedRegistrations / $totalRegistrations) * 100, 1) : 0;
        $attendanceRate = $approvedRegistrations > 0 ? round(($totalAttendance / $approvedRegistrations) * 100, 1) : 0;
        $presentRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : 0;

        // Thống kê điểm rèn luyện
        $totalActivityPoints = $union->eventAttendances()
            ->where('event_attendance.status', 'present')
            ->with('event')
            ->get()
            ->sum(function ($attendance) {
                return $attendance->event->activity_points ?? 0;
            });

        $avgActivityPoints = $presentAttendance > 0 ? round($totalActivityPoints / $presentAttendance, 1) : 0;

        // Thống kê quản lý
        $totalManagers = $union->unionManagers()->count();

        // Sự kiện gần nhất
        $latestEvent = $union->events()->orderBy('start_date', 'desc')->first();
        $latestEventName = $latestEvent ? $latestEvent->title : 'Chưa có sự kiện';
        $latestEventDate = $latestEvent ? $latestEvent->start_date->format('d/m/Y') : 'N/A';

        return [
            // Thông tin cơ bản
            Stat::make('Tên đoàn hội', $union->name)
                ->description('Thông tin đoàn hội')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Quản lý', number_format($totalManagers))
                ->description('Số lượng quản lý')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Sự kiện gần nhất', $latestEventName)
                ->description($latestEventDate)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Điểm TB/SK', $avgActivityPoints . ' điểm')
                ->description('Điểm trung bình mỗi sự kiện')
                ->descriptionIcon('heroicon-m-star')
                ->color('amber'),

            // Thống kê sự kiện
            Stat::make('Tổng sự kiện', number_format($totalEvents))
                ->description('Tất cả sự kiện')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('blue'),

            Stat::make('Đã kết thúc', number_format($completedEvents))
                ->description('Sự kiện hoàn thành')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Đang diễn ra', number_format($ongoingEvents))
                ->description('Sự kiện hiện tại')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('warning'),

            Stat::make('Sắp diễn ra', number_format($upcomingEvents))
                ->description('Sự kiện tương lai')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            // Thống kê đăng ký
            Stat::make('Tổng đăng ký', number_format($totalRegistrations))
                ->description('Tất cả đăng ký')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('indigo'),

            Stat::make('Đã duyệt', number_format($approvedRegistrations))
                ->description($approvalRate . '% tỷ lệ duyệt')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Chờ duyệt', number_format($pendingRegistrations))
                ->description('Đang xử lý')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Từ chối', number_format($rejectedRegistrations))
                ->description('Không được duyệt')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            // Thống kê điểm danh
            Stat::make('Tổng tham gia', number_format($totalAttendance))
                ->description($attendanceRate . '% tỷ lệ tham gia')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('purple'),

            Stat::make('Có mặt', number_format($presentAttendance))
                ->description($presentRate . '% tỷ lệ có mặt')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Vắng mặt', number_format($absentAttendance))
                ->description('Không tham gia')
                ->descriptionIcon('heroicon-m-x-mark')
                ->color('danger'),

            Stat::make('Đi muộn', number_format($lateAttendance))
                ->description('Tham gia muộn')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Thống kê tổng điểm
            Stat::make('Tổng điểm DRL', $totalActivityPoints . ' điểm')
                ->description('Tổng điểm rèn luyện')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('emerald'),
        ];
    }
}

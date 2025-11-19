<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EventDetailStatsWidget extends BaseWidget
{
    public ?Event $record = null;

    protected function getStats(): array
    {
        // Lấy record từ parent page nếu có
        $event = $this->record;
        
        if (!$event) {
            return [];
        }

        // Thống kê đăng ký
        $totalRegistrations = $event->registrations()->count();
        $approvedRegistrations = $event->registrations()->where('status', 'approved')->count();
        $pendingRegistrations = $event->registrations()->where('status', 'pending')->count();
        $rejectedRegistrations = $event->registrations()->where('status', 'rejected')->count();

        // Thống kê điểm danh
        $totalAttendance = $event->attendance()->count();
        $presentCount = $event->attendance()->where('status', 'present')->count();
        $absentCount = $event->attendance()->where('status', 'absent')->count();
        $lateCount = $event->attendance()->where('status', 'late')->count();
        $excusedCount = $event->attendance()->where('status', 'excused')->count();

        // Tính tỷ lệ
        $approvalRate = $totalRegistrations > 0 ? round(($approvedRegistrations / $totalRegistrations) * 100, 1) : 0;
        $attendanceRate = $approvedRegistrations > 0 ? round(($totalAttendance / $approvedRegistrations) * 100, 1) : 0;
        $presentRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        // Thống kê theo giới tính
        $maleCount = $event->attendance()
            ->whereHas('user.student', function ($query) {
                $query->where('gender', 'male');
            })
            ->where('status', 'present')
            ->count();

        $femaleCount = $event->attendance()
            ->whereHas('user.student', function ($query) {
                $query->where('gender', 'female');
            })
            ->where('status', 'present')
            ->count();

        // Thống kê theo khoa
        $facultyStats = $event->attendance()
            ->where('status', 'present')
            ->with('user.student')
            ->get()
            ->groupBy('user.student.faculty')
            ->map->count();

        $topFaculty = $facultyStats->count() > 0 
            ? $facultyStats->sortDesc()->keys()->first() . ' (' . $facultyStats->sortDesc()->first() . ' SV)' 
            : 'N/A';

        return [
            // Thống kê đăng ký
            Stat::make('Tổng đăng ký', number_format($totalRegistrations))
                ->description('Tất cả đăng ký')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),

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
            Stat::make('Tổng điểm danh', number_format($totalAttendance))
                ->description($attendanceRate . '% tỷ lệ tham gia')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make('Có mặt', number_format($presentCount))
                ->description($presentRate . '% tỷ lệ có mặt')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Vắng mặt', number_format($absentCount))
                ->description('Không tham gia')
                ->descriptionIcon('heroicon-m-x-mark')
                ->color('danger'),

            Stat::make('Đi muộn', number_format($lateCount))
                ->description('Tham gia muộn')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Thống kê chi tiết
            Stat::make('Nam', number_format($maleCount))
                ->description('Sinh viên nam có mặt')
                ->descriptionIcon('heroicon-m-user')
                ->color('blue'),

            Stat::make('Nữ', number_format($femaleCount))
                ->description('Sinh viên nữ có mặt')
                ->descriptionIcon('heroicon-m-user')
                ->color('pink'),

            Stat::make('Khoa hàng đầu', $topFaculty)
                ->description('Khoa tham gia nhiều nhất')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('indigo'),

            Stat::make('Điểm rèn luyện', ($event->activity_points ?? 0) . ' điểm')
                ->description('Điểm cho sự kiện này')
                ->descriptionIcon('heroicon-m-star')
                ->color('blue'),
        ];
    }
}

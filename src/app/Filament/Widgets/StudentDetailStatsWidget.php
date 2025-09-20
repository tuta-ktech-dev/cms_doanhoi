<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentDetailStatsWidget extends BaseWidget
{
    public ?Student $record = null;

    protected function getStats(): array
    {
        // Lấy record từ parent page nếu có
        $student = $this->record ?? $this->getRecord();
        
        if (!$student) {
            return [];
        }

        // Thống kê đăng ký sự kiện
        $totalRegistrations = $student->eventRegistrations()->count();
        $approvedRegistrations = $student->eventRegistrations()->where('status', 'approved')->count();
        $pendingRegistrations = $student->eventRegistrations()->where('status', 'pending')->count();
        $rejectedRegistrations = $student->eventRegistrations()->where('status', 'rejected')->count();

        // Thống kê tham gia sự kiện
        $totalAttendance = $student->eventAttendances()->count();
        $presentEvents = $student->eventAttendances()->where('status', 'present')->count();
        $absentEvents = $student->eventAttendances()->where('status', 'absent')->count();
        $lateEvents = $student->eventAttendances()->where('status', 'late')->count();
        $excusedEvents = $student->eventAttendances()->where('status', 'excused')->count();

        // Tính tỷ lệ
        $approvalRate = $totalRegistrations > 0 ? round(($approvedRegistrations / $totalRegistrations) * 100, 1) : 0;
        $attendanceRate = $approvedRegistrations > 0 ? round(($totalAttendance / $approvedRegistrations) * 100, 1) : 0;
        $presentRate = $totalAttendance > 0 ? round(($presentEvents / $totalAttendance) * 100, 1) : 0;

        // Tính tổng điểm rèn luyện từ sự kiện
        $totalActivityPoints = $student->eventAttendances()
            ->where('status', 'present')
            ->with('event')
            ->get()
            ->sum(function ($attendance) {
                return $attendance->event->activity_points ?? 0;
            });

        // Sự kiện gần nhất
        $latestAttendance = $student->eventAttendances()
            ->with('event')
            ->orderBy('attended_at', 'desc')
            ->first();

        $latestEventName = $latestAttendance ? $latestAttendance->event->title : 'Chưa tham gia';
        $latestEventDate = $latestAttendance ? $latestAttendance->attended_at->format('d/m/Y') : 'N/A';

        return [
            // Thông tin cơ bản
            Stat::make('MSSV', $student->student_id)
                ->description('Mã số sinh viên')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),

            Stat::make('Lớp', $student->class ?? 'N/A')
                ->description($student->faculty ?? 'N/A')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Khóa', $student->course ?? 'N/A')
                ->description('Khóa học')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Điểm hiện tại', ($student->activity_points ?? 0) . ' điểm')
                ->description('Điểm rèn luyện trong profile')
                ->descriptionIcon('heroicon-m-star')
                ->color('amber'),

            // Thống kê đăng ký
            Stat::make('Tổng đăng ký', number_format($totalRegistrations))
                ->description('Sự kiện đã đăng ký')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('blue'),

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

            // Thống kê tham gia
            Stat::make('Tổng tham gia', number_format($totalAttendance))
                ->description($attendanceRate . '% tỷ lệ tham gia')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('indigo'),

            Stat::make('Có mặt', number_format($presentEvents))
                ->description($presentRate . '% tỷ lệ có mặt')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Vắng mặt', number_format($absentEvents))
                ->description('Không tham gia')
                ->descriptionIcon('heroicon-m-x-mark')
                ->color('danger'),

            Stat::make('Đi muộn', number_format($lateEvents))
                ->description('Tham gia muộn')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Thống kê điểm rèn luyện
            Stat::make('Điểm từ SK', $totalActivityPoints . ' điểm')
                ->description('Tổng điểm từ sự kiện')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('emerald'),

            Stat::make('SK gần nhất', $latestEventName)
                ->description($latestEventDate)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('purple'),
        ];
    }
}

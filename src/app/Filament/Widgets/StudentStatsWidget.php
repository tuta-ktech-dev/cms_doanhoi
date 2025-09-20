<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\Event;
use App\Models\EventAttendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Tổng số sinh viên
        $totalStudents = Student::count();
        
        // Sinh viên theo giới tính
        $maleStudents = Student::where('gender', 'male')->count();
        $femaleStudents = Student::where('gender', 'female')->count();
        
        // Sinh viên theo khoa
        $facultyStats = Student::selectRaw('faculty, COUNT(*) as count')
            ->groupBy('faculty')
            ->orderByDesc('count')
            ->get();
        
        $topFaculty = $facultyStats->first();
        $topFacultyName = $topFaculty ? $topFaculty->faculty . ' (' . $topFaculty->count . ' SV)' : 'N/A';
        
        // Sinh viên có hoạt động
        $activeStudents = Student::whereHas('eventAttendances')->count();
        $activeRate = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 1) : 0;
        
        // Thống kê điểm rèn luyện
        $avgActivityPoints = Student::avg('activity_points') ?? 0;
        $highActivityStudents = Student::where('activity_points', '>=', 80)->count();
        
        // Thống kê sự kiện
        $totalEvents = Event::count();
        $completedEvents = Event::where('end_date', '<', now())->count();
        
        // Thống kê tổng quan
        $totalAttendance = EventAttendance::count();
        $totalPresent = EventAttendance::where('status', 'present')->count();
        $overallAttendanceRate = $totalAttendance > 0 ? round(($totalPresent / $totalAttendance) * 100, 1) : 0;

        return [
            Stat::make('Tổng sinh viên', number_format($totalStudents))
                ->description('Tất cả sinh viên trong hệ thống')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Nam', number_format($maleStudents))
                ->description('Sinh viên nam')
                ->descriptionIcon('heroicon-m-user')
                ->color('blue'),

            Stat::make('Nữ', number_format($femaleStudents))
                ->description('Sinh viên nữ')
                ->descriptionIcon('heroicon-m-user')
                ->color('pink'),

            Stat::make('Khoa hàng đầu', $topFacultyName)
                ->description('Khoa có nhiều sinh viên nhất')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('indigo'),

            Stat::make('Sinh viên hoạt động', number_format($activeStudents))
                ->description($activeRate . '% tỷ lệ hoạt động')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Điểm TB', round($avgActivityPoints, 1) . ' điểm')
                ->description('Điểm rèn luyện trung bình')
                ->descriptionIcon('heroicon-m-star')
                ->color('amber'),

            Stat::make('Điểm cao', number_format($highActivityStudents))
                ->description('SV có điểm ≥ 80')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('emerald'),

            Stat::make('Tổng sự kiện', number_format($totalEvents))
                ->description($completedEvents . ' đã kết thúc')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('purple'),

            Stat::make('Tỷ lệ có mặt', $overallAttendanceRate . '%')
                ->description('Tỷ lệ có mặt tổng thể')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($overallAttendanceRate >= 80 ? 'success' : ($overallAttendanceRate >= 60 ? 'warning' : 'danger')),
        ];
    }
}

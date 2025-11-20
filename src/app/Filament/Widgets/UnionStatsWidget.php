<?php

namespace App\Filament\Widgets;

use App\Models\Union;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnionStatsWidget extends BaseWidget
{
    protected ?string $heading = 'Thống kê Đoàn Hội';

    protected function getStats(): array
    {
        // Tổng số đoàn hội
        $totalUnions = Union::count();

        // Thống kê sự kiện theo đoàn hội
        $totalEvents = Event::count();
        $completedEvents = Event::where('end_date', '<', now())->count();
        $ongoingEvents = Event::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
        $upcomingEvents = Event::where('start_date', '>', now())->count();

        // Thống kê đăng ký và tham gia
        $totalRegistrations = EventRegistration::where('status', 'approved')->count();
        $totalAttendance = EventAttendance::count();
        $presentAttendance = EventAttendance::where('status', 'present')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : 0;

        // Thống kê sinh viên theo đoàn hội
        $studentsInUnions = Student::whereHas('eventAttendances.event', function ($query) {
            $query->whereNotNull('union_id');
        })->count();

        $totalStudents = Student::count();
        $activeStudentRate = $totalStudents > 0 ? round(($studentsInUnions / $totalStudents) * 100, 1) : 0;

        // Đoàn hội hoạt động nhất
        $mostActiveUnion = Union::withCount(['events', 'eventRegistrations', 'eventAttendances'])
            ->get()
            ->sortByDesc(function ($union) {
                return $union->events_count + $union->event_registrations_count + $union->event_attendances_count;
            })
            ->first();

        $mostActiveUnionName = $mostActiveUnion ? $mostActiveUnion->name : 'N/A';
        $mostActiveUnionEvents = $mostActiveUnion ? $mostActiveUnion->events_count : 0;

        // Thống kê điểm rèn luyện từ sự kiện
        $totalActivityPoints = EventAttendance::where('status', 'present')
            ->with('event')
            ->get()
            ->sum(function ($attendance) {
                return $attendance->event->activity_points ?? 0;
            });

        $avgActivityPoints = $presentAttendance > 0 ? round($totalActivityPoints / $presentAttendance, 1) : 0;

        return [
            Stat::make('Tổng đoàn hội', number_format($totalUnions))
                ->description('Tất cả đoàn hội trong hệ thống')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Tổng sự kiện', number_format($totalEvents))
                ->description($completedEvents . ' đã kết thúc')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Đang diễn ra', number_format($ongoingEvents))
                ->description('Sự kiện hiện tại')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('warning'),

            Stat::make('Sắp diễn ra', number_format($upcomingEvents))
                ->description('Sự kiện tương lai')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),

            Stat::make('Tổng đăng ký', number_format($totalRegistrations))
                ->description('Đăng ký đã duyệt')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('blue'),

            Stat::make('Tổng tham gia', number_format($totalAttendance))
                ->description('Sinh viên tham gia')
                ->descriptionIcon('heroicon-m-users')
                ->color('indigo'),

            Stat::make('Tỷ lệ có mặt', $attendanceRate . '%')
                ->description('Có mặt / Tổng tham gia')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger')),

            Stat::make('SV hoạt động', number_format($studentsInUnions))
                ->description($activeStudentRate . '% tỷ lệ hoạt động')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('purple'),

            Stat::make('Điểm TB/SK', $avgActivityPoints . ' điểm')
                ->description('Điểm trung bình mỗi sự kiện')
                ->descriptionIcon('heroicon-m-star')
                ->color('emerald'),
        ];
    }
}

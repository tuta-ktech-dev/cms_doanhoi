<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EventTimelineWidget extends ChartWidget
{
    public ?Event $record = null;

    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return 'Timeline đăng ký và điểm danh';
    }

    protected function getData(): array
    {
        // Lấy record từ parent page nếu có
        $event = $this->record;
        
        if (!$event) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Lấy dữ liệu đăng ký theo ngày (7 ngày gần nhất)
        $registrationData = $event->registrations()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Lấy dữ liệu điểm danh theo ngày
        $attendanceData = $event->attendance()
            ->select(DB::raw('DATE(attended_at) as date'), DB::raw('count(*) as count'))
            ->where('attended_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Tạo labels cho 7 ngày gần nhất
        $labels = [];
        $registrationCounts = [];
        $attendanceCounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dateLabel = now()->subDays($i)->format('d/m');
            
            $labels[] = $dateLabel;
            $registrationCounts[] = $registrationData[$date] ?? 0;
            $attendanceCounts[] = $attendanceData[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Đăng ký',
                    'data' => $registrationCounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Điểm danh',
                    'data' => $attendanceCounts,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => '#10B981',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ];
    }
}

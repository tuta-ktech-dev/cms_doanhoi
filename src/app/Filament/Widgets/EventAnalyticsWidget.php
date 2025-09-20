<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EventAnalyticsWidget extends ChartWidget
{
    public ?Event $record = null;

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Phân tích sự kiện';
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

        // Thống kê theo trạng thái điểm danh
        $attendanceStats = $event->attendance()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        $statusMap = [
            'present' => ['label' => 'Có mặt', 'color' => '#10B981'],
            'absent' => ['label' => 'Vắng mặt', 'color' => '#EF4444'],
            'late' => ['label' => 'Đi muộn', 'color' => '#F59E0B'],
            'excused' => ['label' => 'Có phép', 'color' => '#3B82F6'],
        ];

        foreach ($statusMap as $status => $config) {
            $count = $attendanceStats[$status] ?? 0;
            if ($count > 0) {
                $labels[] = $config['label'];
                $data[] = $count;
                $colors[] = $config['color'];
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Số lượng',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }"
                    ]
                ]
            ],
        ];
    }
}

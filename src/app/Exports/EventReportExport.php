<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EventReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function query()
    {
        $query = Event::with(['union', 'registrations', 'attendance'])
            ->where('end_date', '<', now()) // Chỉ lấy sự kiện đã kết thúc
            ->orderBy('end_date', 'desc');

        // Áp dụng quyền hạn
        if ($this->user->isUnionManager()) {
            $userUnionIds = $this->user->unionManager->pluck('union_id')->toArray();
            $query->whereIn('union_id', $userUnionIds);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên sự kiện',
            'Đoàn hội',
            'Ngày bắt đầu',
            'Ngày kết thúc',
            'Địa điểm',
            'Số lượng đăng ký tối đa',
            'Số lượng đăng ký thực tế',
            'Số lượng tham gia',
            'Số lượng có mặt',
            'Số lượng vắng mặt',
            'Tỷ lệ tham gia (%)',
            'Tỷ lệ có mặt (%)',
            'Điểm rèn luyện',
            'Trạng thái',
            'Ngày tạo',
        ];
    }

    public function map($event): array
    {
        static $index = 0;
        $index++;

        $totalRegistrations = $event->registrations()->where('status', 'approved')->count();
        $totalAttendance = $event->attendance()->count();
        $presentCount = $event->attendance()->where('status', 'present')->count();
        $absentCount = $event->attendance()->where('status', 'absent')->count();
        
        $attendanceRate = $totalRegistrations > 0 ? round(($totalAttendance / $totalRegistrations) * 100, 2) : 0;
        $presentRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;

        return [
            $index,
            $event->title,
            $event->union->name,
            $event->start_date->format('d/m/Y H:i'),
            $event->end_date->format('d/m/Y H:i'),
            $event->location ?? 'N/A',
            $event->max_participants ?? 'Không giới hạn',
            $totalRegistrations,
            $totalAttendance,
            $presentCount,
            $absentCount,
            $attendanceRate . '%',
            $presentRate . '%',
            $event->activity_points ?? '0.00',
            $event->status === 'published' ? 'Đã xuất bản' : 'Bản nháp',
            $event->created_at->format('d/m/Y H:i'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 35,  // Tên sự kiện
            'C' => 20,  // Đoàn hội
            'D' => 18,  // Ngày bắt đầu
            'E' => 18,  // Ngày kết thúc
            'F' => 25,  // Địa điểm
            'G' => 20,  // Số lượng đăng ký tối đa
            'H' => 20,  // Số lượng đăng ký thực tế
            'I' => 18,  // Số lượng tham gia
            'J' => 18,  // Số lượng có mặt
            'K' => 18,  // Số lượng vắng mặt
            'L' => 15,  // Tỷ lệ tham gia
            'M' => 15,  // Tỷ lệ có mặt
            'N' => 15,  // Điểm rèn luyện
            'O' => 15,  // Trạng thái
            'P' => 20,  // Ngày tạo
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'FFF3E0',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add borders to all cells
                $sheet->getStyle('A1:P' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Auto-fit row heights
                foreach ($sheet->getRowIterator() as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(-1);
                }

                // Add title
                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:P1');
                $sheet->setCellValue('A1', 'BÁO CÁO THỐNG KÊ SỰ KIỆN ĐÃ KẾT THÚC');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Add summary info
                $sheet->mergeCells('A2:P2');
                $sheet->setCellValue('A2', 'Thống kê các sự kiện đã kết thúc - Bao gồm số liệu đăng ký, tham gia và điểm danh');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // Add export info
                $sheet->mergeCells('A3:P3');
                $sheet->setCellValue('A3', 'Xuất ngày: ' . now()->format('d/m/Y H:i') . ' - Người xuất: ' . $this->user->full_name);
                $sheet->getStyle('A3')->getFont()->setSize(10);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(3)->setRowHeight(20);
            },
        ];
    }
}

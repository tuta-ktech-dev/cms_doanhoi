<?php

namespace App\Exports;

use App\Models\EventAttendance;
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

class EventAttendanceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $eventId;
    protected $user;

    public function __construct($eventId = null)
    {
        $this->eventId = $eventId;
        $this->user = auth()->user();
    }

    public function query()
    {
        $query = EventAttendance::with(['user.student', 'event.union', 'registeredBy']);

        if ($this->eventId) {
            $query->where('event_id', $this->eventId);
        }

        // Áp dụng quyền hạn
        if ($this->user->isUnionManager()) {
            $userUnionIds = $this->user->unionManager->pluck('union_id')->toArray();
            $query->whereHas('event', function ($q) use ($userUnionIds) {
                $q->whereIn('union_id', $userUnionIds);
            });
        }

        return $query->orderBy('attended_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'STT',
            'Họ và tên',
            'Email',
            'MSSV',
            'Lớp',
            'Khoa',
            'Điểm rèn luyện (sự kiện)',
            'Sự kiện',
            'Đoàn hội',
            'Trạng thái',
            'Thời gian điểm danh',
            'Người điểm danh',
            'Ghi chú',
            'Ngày tạo',
        ];
    }

    public function map($attendance): array
    {
        static $index = 0;
        $index++;

        // Tính điểm rèn luyện dựa trên sự kiện này
        $eventActivityPoints = $attendance->event->activity_points ?? 0;
        $earnedPoints = $attendance->status === 'present' ? $eventActivityPoints : 0;

        return [
            $index,
            $attendance->user->full_name,
            $attendance->user->email,
            $attendance->user->student?->student_id ?? 'N/A',
            $attendance->user->student?->class ?? 'N/A',
            $attendance->user->student?->faculty ?? 'N/A',
            $earnedPoints . ' điểm',
            $attendance->event->title,
            $attendance->event->union->name,
            $attendance->status_label,
            $attendance->attended_at->format('d/m/Y H:i'),
            $attendance->registeredBy?->full_name ?? 'Hệ thống',
            $attendance->notes ?? '',
            $attendance->created_at->format('d/m/Y H:i'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 25,  // Họ và tên
            'C' => 30,  // Email
            'D' => 12,  // MSSV
            'E' => 15,  // Lớp
            'F' => 20,  // Khoa
            'G' => 15,  // Điểm rèn luyện
            'H' => 35,  // Sự kiện
            'I' => 20,  // Đoàn hội
            'J' => 15,  // Trạng thái
            'K' => 20,  // Thời gian điểm danh
            'L' => 20,  // Người điểm danh
            'M' => 30,  // Ghi chú
            'N' => 20,  // Ngày tạo
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
                        'rgb' => 'E3F2FD',
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
                $sheet->getStyle('A1:N' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Auto-fit row heights
                foreach ($sheet->getRowIterator() as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(-1);
                }

                // Add title
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:N1');
                $sheet->setCellValue('A1', 'BÁO CÁO ĐIỂM DANH SỰ KIỆN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Add export info
                $sheet->mergeCells('A2:N2');
                $sheet->setCellValue('A2', 'Xuất ngày: ' . now()->format('d/m/Y H:i') . ' - Người xuất: ' . $this->user->full_name);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(20);
            },
        ];
    }
}
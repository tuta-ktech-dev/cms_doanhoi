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

class EventAttendanceByEventExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $eventId;
    protected $user;
    protected $event;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
        $this->user = auth()->user();
        $this->event = \App\Models\Event::with('union')->find($eventId);
    }

    public function query()
    {
        $query = EventAttendance::with(['user.student', 'event.union', 'registeredBy'])
            ->where('event_id', $this->eventId);

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
            'Điểm rèn luyện',
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

        return [
            $index,
            $attendance->user->full_name,
            $attendance->user->email,
            $attendance->user->student?->student_id ?? 'N/A',
            $attendance->user->student?->class ?? 'N/A',
            $attendance->user->student?->faculty ?? 'N/A',
            $attendance->user->student?->activity_points ?? '0.00',
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
            'H' => 15,  // Trạng thái
            'I' => 20,  // Thời gian điểm danh
            'J' => 20,  // Người điểm danh
            'K' => 30,  // Ghi chú
            'L' => 20,  // Ngày tạo
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
                $sheet->getStyle('A1:L' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Auto-fit row heights
                foreach ($sheet->getRowIterator() as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(-1);
                }

                // Add title
                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'BÁO CÁO ĐIỂM DANH SỰ KIỆN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Add event info
                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'Sự kiện: ' . $this->event->title . ' - Đoàn hội: ' . $this->event->union->name);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // Add export info
                $sheet->mergeCells('A3:L3');
                $sheet->setCellValue('A3', 'Xuất ngày: ' . now()->format('d/m/Y H:i') . ' - Người xuất: ' . $this->user->full_name);
                $sheet->getStyle('A3')->getFont()->setSize(10);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(3)->setRowHeight(20);
            },
        ];
    }
}

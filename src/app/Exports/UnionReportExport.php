<?php

namespace App\Exports;

use App\Models\Union;
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

class UnionReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function query()
    {
        return Union::with(['events', 'eventRegistrations', 'eventAttendances', 'unionManagers'])
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên đoàn hội',
            'Mô tả',
            'Số quản lý',
            'Tổng sự kiện',
            'Sự kiện đã kết thúc',
            'Sự kiện đang diễn ra',
            'Sự kiện sắp diễn ra',
            'Tổng đăng ký',
            'Đăng ký đã duyệt',
            'Đăng ký chờ duyệt',
            'Đăng ký từ chối',
            'Tỷ lệ duyệt (%)',
            'Tổng tham gia',
            'Có mặt',
            'Vắng mặt',
            'Đi muộn',
            'Có phép',
            'Tỷ lệ tham gia (%)',
            'Tỷ lệ có mặt (%)',
            'Tổng điểm DRL',
            'Điểm TB/SK',
            'Ngày tạo',
        ];
    }

    public function map($union): array
    {
        static $index = 0;
        $index++;

        // Thống kê sự kiện
        $totalEvents = $union->events->count();
        $completedEvents = $union->events->where('end_date', '<', now())->count();
        $ongoingEvents = $union->events
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();
        $upcomingEvents = $union->events->where('start_date', '>', now())->count();

        // Thống kê đăng ký
        $totalRegistrations = $union->eventRegistrations->count();
        $approvedRegistrations = $union->eventRegistrations->where('status', 'approved')->count();
        $pendingRegistrations = $union->eventRegistrations->where('status', 'pending')->count();
        $rejectedRegistrations = $union->eventRegistrations->where('status', 'rejected')->count();

        // Thống kê điểm danh
        $totalAttendance = $union->eventAttendances->count();
        $presentAttendance = $union->eventAttendances->where('status', 'present')->count();
        $absentAttendance = $union->eventAttendances->where('status', 'absent')->count();
        $lateAttendance = $union->eventAttendances->where('status', 'late')->count();
        $excusedAttendance = $union->eventAttendances->where('status', 'excused')->count();

        // Tính tỷ lệ
        $approvalRate = $totalRegistrations > 0 ? round(($approvedRegistrations / $totalRegistrations) * 100, 2) : 0;
        $attendanceRate = $approvedRegistrations > 0 ? round(($totalAttendance / $approvedRegistrations) * 100, 2) : 0;
        $presentRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 2) : 0;

        // Thống kê điểm rèn luyện
        $totalActivityPoints = $union->eventAttendances
            ->where('status', 'present')
            ->sum(function ($attendance) {
                return $attendance->event->activity_points ?? 0;
            });

        $avgActivityPoints = $presentAttendance > 0 ? round($totalActivityPoints / $presentAttendance, 2) : 0;

        return [
            $index,
            $union->name,
            $union->description ?? 'N/A',
            $union->unionManagers->count(),
            $totalEvents,
            $completedEvents,
            $ongoingEvents,
            $upcomingEvents,
            $totalRegistrations,
            $approvedRegistrations,
            $pendingRegistrations,
            $rejectedRegistrations,
            $approvalRate . '%',
            $totalAttendance,
            $presentAttendance,
            $absentAttendance,
            $lateAttendance,
            $excusedAttendance,
            $attendanceRate . '%',
            $presentRate . '%',
            $totalActivityPoints . ' điểm',
            $avgActivityPoints . ' điểm',
            $union->created_at->format('d/m/Y H:i'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 25,  // Tên đoàn hội
            'C' => 40,  // Mô tả
            'D' => 12,  // Số quản lý
            'E' => 12,  // Tổng sự kiện
            'F' => 15,  // Sự kiện đã kết thúc
            'G' => 15,  // Sự kiện đang diễn ra
            'H' => 15,  // Sự kiện sắp diễn ra
            'I' => 12,  // Tổng đăng ký
            'J' => 15,  // Đăng ký đã duyệt
            'K' => 15,  // Đăng ký chờ duyệt
            'L' => 15,  // Đăng ký từ chối
            'M' => 12,  // Tỷ lệ duyệt
            'N' => 12,  // Tổng tham gia
            'O' => 10,  // Có mặt
            'P' => 10,  // Vắng mặt
            'Q' => 10,  // Đi muộn
            'R' => 10,  // Có phép
            'S' => 15,  // Tỷ lệ tham gia
            'T' => 15,  // Tỷ lệ có mặt
            'U' => 15,  // Tổng điểm DRL
            'V' => 12,  // Điểm TB/SK
            'W' => 20,  // Ngày tạo
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
                        'rgb' => 'F0FDF4',
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
                $sheet->getStyle('A1:W' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Auto-fit row heights
                foreach ($sheet->getRowIterator() as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(-1);
                }

                // Add title
                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:W1');
                $sheet->setCellValue('A1', 'BÁO CÁO THỐNG KÊ ĐOÀN HỘI');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Add summary info
                $sheet->mergeCells('A2:W2');
                $sheet->setCellValue('A2', 'Thống kê chi tiết hoạt động của các đoàn hội - Bao gồm sự kiện, đăng ký, điểm danh và điểm rèn luyện');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // Add export info
                $sheet->mergeCells('A3:W3');
                $sheet->setCellValue('A3', 'Xuất ngày: ' . now()->format('d/m/Y H:i') . ' - Người xuất: ' . $this->user->full_name);
                $sheet->getStyle('A3')->getFont()->setSize(10);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(3)->setRowHeight(20);
            },
        ];
    }
}

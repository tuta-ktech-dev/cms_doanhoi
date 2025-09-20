<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
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

class ComprehensiveDataExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function query()
    {
        return Student::with([
            'user', 
            'eventRegistrations.event.union', 
            'eventAttendances.event'
        ])->orderBy('student_id');
    }

    public function headings(): array
    {
        return [
            'STT',
            'MSSV',
            'Họ và tên',
            'Email',
            'Lớp',
            'Khoa',
            'Khóa',
            'Giới tính',
            'Ngày sinh',
            'Điểm rèn luyện từ SK',
            'Tổng sự kiện đăng ký',
            'Tổng sự kiện tham gia',
            'Tổng sự kiện có mặt',
            'Tổng sự kiện vắng mặt',
            'Tỷ lệ tham gia (%)',
            'Tỷ lệ có mặt (%)',
            'Tổng điểm rèn luyện từ SK',
            'Sự kiện gần nhất',
            'Ngày tạo',
        ];
    }

    public function map($student): array
    {
        static $index = 0;
        $index++;

        // Thống kê đăng ký
        $totalRegistrations = $student->eventRegistrations->count();
        $approvedRegistrations = $student->eventRegistrations->where('status', 'approved')->count();

        // Thống kê tham gia
        $totalAttendance = $student->eventAttendances->count();
        $presentEvents = $student->eventAttendances->where('status', 'present')->count();
        $absentEvents = $student->eventAttendances->where('status', 'absent')->count();

        // Tính tỷ lệ
        $attendanceRate = $approvedRegistrations > 0 ? round(($totalAttendance / $approvedRegistrations) * 100, 2) : 0;
        $presentRate = $totalAttendance > 0 ? round(($presentEvents / $totalAttendance) * 100, 2) : 0;

        // Tính tổng điểm rèn luyện từ sự kiện
        $totalActivityPoints = $student->eventAttendances
            ->where('status', 'present')
            ->sum(function ($attendance) {
                return $attendance->event->activity_points ?? 0;
            });

        // Sự kiện gần nhất
        $latestEvent = $student->eventAttendances
            ->sortByDesc('attended_at')
            ->first();
        $latestEventName = $latestEvent ? $latestEvent->event->title : 'Chưa tham gia';

        return [
            $index,
            $student->student_id,
            $student->user->full_name,
            $student->user->email,
            $student->class ?? 'N/A',
            $student->faculty ?? 'N/A',
            $student->course ?? 'N/A',
            $student->gender === 'male' ? 'Nam' : ($student->gender === 'female' ? 'Nữ' : 'Khác'),
            $student->date_of_birth?->format('d/m/Y') ?? 'N/A',
            $totalActivityPoints . ' điểm',
            $totalRegistrations,
            $totalAttendance,
            $presentEvents,
            $absentEvents,
            $attendanceRate . '%',
            $presentRate . '%',
            $totalActivityPoints . ' điểm',
            $latestEventName,
            $student->created_at->format('d/m/Y H:i'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 12,  // MSSV
            'C' => 25,  // Họ và tên
            'D' => 30,  // Email
            'E' => 15,  // Lớp
            'F' => 20,  // Khoa
            'G' => 8,   // Khóa
            'H' => 10,  // Giới tính
            'I' => 12,  // Ngày sinh
            'J' => 18,  // Điểm rèn luyện từ SK
            'K' => 18,  // Tổng sự kiện đăng ký
            'L' => 18,  // Tổng sự kiện tham gia
            'M' => 18,  // Tổng sự kiện có mặt
            'N' => 18,  // Tổng sự kiện vắng mặt
            'O' => 15,  // Tỷ lệ tham gia
            'P' => 15,  // Tỷ lệ có mặt
            'Q' => 20,  // Tổng điểm rèn luyện từ SK
            'R' => 35,  // Sự kiện gần nhất
            'S' => 20,  // Ngày tạo
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
                        'rgb' => 'F0F9FF',
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
                $sheet->getStyle('A1:S' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Auto-fit row heights
                foreach ($sheet->getRowIterator() as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(-1);
                }

                // Add title
                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:S1');
                $sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP TOÀN HỆ THỐNG');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Add summary info
                $sheet->mergeCells('A2:S2');
                $sheet->setCellValue('A2', 'Bao gồm: Sinh viên, Sự kiện, Đăng ký, Điểm danh, Thống kê tổng hợp');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // Add export info
                $sheet->mergeCells('A3:S3');
                $sheet->setCellValue('A3', 'Xuất ngày: ' . now()->format('d/m/Y H:i') . ' - Người xuất: ' . $this->user->full_name);
                $sheet->getStyle('A3')->getFont()->setSize(10);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(3)->setRowHeight(20);
            },
        ];
    }
}

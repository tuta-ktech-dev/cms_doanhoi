<?php

namespace App\Exports;

use App\Models\Student;
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

class AllStudentsAttendanceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function query()
    {
        $query = Student::with(['user', 'eventAttendances.event.union']);

        // Áp dụng quyền hạn - chỉ hiển thị sinh viên thuộc các đoàn hội mà user quản lý
        if ($this->user->isUnionManager()) {
            $userUnionIds = $this->user->unionManager->pluck('union_id')->toArray();
            $query->whereHas('eventAttendances.event', function ($q) use ($userUnionIds) {
                $q->whereIn('union_id', $userUnionIds);
            });
        }

        return $query->orderBy('student_id');
    }

    public function headings(): array
    {
        return [
            'STT',
            'Họ và tên',
            'Email',
            'MSSV',
            'Ngày sinh',
            'Giới tính',
            'Lớp',
            'Khoa',
            'Khóa',
            'Điểm rèn luyện',
            'Tổng sự kiện tham gia',
            'Tổng sự kiện có mặt',
            'Tổng sự kiện vắng mặt',
            'Tỷ lệ tham gia (%)',
            'Ngày tạo',
        ];
    }

    public function map($student): array
    {
        static $index = 0;
        $index++;

        $totalEvents = $student->eventAttendances->count();
        $presentEvents = $student->eventAttendances->where('status', 'present')->count();
        $absentEvents = $student->eventAttendances->where('status', 'absent')->count();
        $attendanceRate = $totalEvents > 0 ? round(($presentEvents / $totalEvents) * 100, 2) : 0;

        return [
            $index,
            $student->user->full_name,
            $student->user->email,
            $student->student_id,
            $student->date_of_birth?->format('d/m/Y') ?? 'N/A',
            $student->gender === 'male' ? 'Nam' : ($student->gender === 'female' ? 'Nữ' : 'N/A'),
            $student->class ?? 'N/A',
            $student->faculty ?? 'N/A',
            $student->course ?? 'N/A',
            $student->activity_points ?? '0.00',
            $totalEvents,
            $presentEvents,
            $absentEvents,
            $attendanceRate . '%',
            $student->created_at->format('d/m/Y H:i'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 25,  // Họ và tên
            'C' => 30,  // Email
            'D' => 12,  // MSSV
            'E' => 12,  // Ngày sinh
            'F' => 10,  // Giới tính
            'G' => 15,  // Lớp
            'H' => 20,  // Khoa
            'I' => 8,   // Khóa
            'J' => 15,  // Điểm rèn luyện
            'K' => 18,  // Tổng sự kiện tham gia
            'L' => 18,  // Tổng sự kiện có mặt
            'M' => 18,  // Tổng sự kiện vắng mặt
            'N' => 15,  // Tỷ lệ tham gia
            'O' => 20,  // Ngày tạo
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
                        'rgb' => 'E8F5E8',
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
                $sheet->getStyle('A1:O' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Auto-fit row heights
                foreach ($sheet->getRowIterator() as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(-1);
                }

                // Add title
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:O1');
                $sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP SINH VIÊN VÀ ĐIỂM DANH');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Add export info
                $sheet->mergeCells('A2:O2');
                $sheet->setCellValue('A2', 'Xuất ngày: ' . now()->format('d/m/Y H:i') . ' - Người xuất: ' . $this->user->full_name);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(20);
            },
        ];
    }
}

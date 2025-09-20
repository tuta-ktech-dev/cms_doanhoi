<?php

namespace App\Filament\Resources\Students\Pages;

use App\Exports\AllStudentsAttendanceExport;
use App\Exports\ComprehensiveDataExport;
use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_students')
                ->label('Xuất sinh viên')
                ->icon('heroicon-o-users')
                ->color('primary')
                ->action(function () {
                    $fileName = 'danh_sach_sinh_vien_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new AllStudentsAttendanceExport(), $fileName);
                }),

            Action::make('export_comprehensive')
                ->label('Xuất tổng hợp')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $fileName = 'bao_cao_tong_hop_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new ComprehensiveDataExport(), $fileName);
                }),

            Action::make('export_all_events')
                ->label('Xuất tất cả sự kiện')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->action(function () {
                    $fileName = 'tat_ca_su_kien_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new \App\Exports\EventReportExport(), $fileName);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StudentStatsWidget::class,
        ];
    }
}
<?php

namespace App\Filament\Resources\Unions\Pages;

use App\Exports\UnionReportExport;
use App\Filament\Resources\Unions\UnionResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListUnions extends ListRecords
{
    protected static string $resource = UnionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tạo đoàn hội'),

            Action::make('export_unions')
                ->label('Xuất báo cáo đoàn hội')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $fileName = 'bao_cao_doan_hoi_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new UnionReportExport(), $fileName);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\UnionStatsWidget::class,
        ];
    }
}
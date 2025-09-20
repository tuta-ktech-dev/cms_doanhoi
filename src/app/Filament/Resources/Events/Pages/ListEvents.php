<?php

namespace App\Filament\Resources\Events\Pages;

use App\Exports\EventReportExport;
use App\Filament\Resources\Events\EventResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tạo sự kiện'),
            
            Action::make('export_report')
                ->label('Báo cáo sự kiện')
                ->icon('heroicon-o-chart-bar')
                ->color('warning')
                ->action(function () {
                    $fileName = 'bao_cao_su_kien_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new EventReportExport(), $fileName);
                }),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin xem tất cả sự kiện
            return parent::getTableQuery();
        }
        
        if ($user->isUnionManager()) {
            // Union Manager chỉ xem sự kiện của đoàn hội mình quản lý
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return parent::getTableQuery()->whereIn('union_id', $userUnionIds);
        }
        
        return parent::getTableQuery();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\EventStatsWidget::class,
        ];
    }
}

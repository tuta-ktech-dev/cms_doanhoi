<?php

namespace App\Filament\Resources\EventAttendances\Pages;

use App\Exports\EventAttendanceExport;
use App\Exports\EventAttendanceByEventExport;
use App\Exports\AllStudentsAttendanceExport;
use App\Filament\Resources\EventAttendances\EventAttendanceResource;
use App\Models\Event;
use App\Models\Union;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Maatwebsite\Excel\Facades\Excel;

class ListEventAttendances extends ListRecords
{
    protected static string $resource = EventAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm điểm danh'),
            
            Action::make('export_all')
                ->label('Xuất tất cả')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $fileName = 'diem_danh_tat_ca_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new EventAttendanceExport(), $fileName);
                }),
            
            Action::make('export_by_event')
                ->label('Xuất theo sự kiện')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->form([
                    Select::make('event_id')
                        ->label('Chọn sự kiện')
                        ->options($this->getEventOptions())
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $event = Event::find($data['event_id']);
                    $fileName = 'diem_danh_' . \Str::slug($event->title) . '_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new EventAttendanceByEventExport($data['event_id']), $fileName);
                }),
            
            Action::make('export_students')
                ->label('Xuất sinh viên')
                ->icon('heroicon-o-users')
                ->color('warning')
                ->action(function () {
                    $fileName = 'sinh_vien_diem_danh_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                    
                    return Excel::download(new AllStudentsAttendanceExport(), $fileName);
                }),
        ];
    }

    // Tạm thời comment out getTabs() vì có thể không được hỗ trợ trong Filament v4
    // public function getTabs(): array
    // {
    //     $user = auth()->user();
        
    //     if ($user->isAdmin()) {
    //         // Admin có thể xem tất cả
    //         $events = Event::with('union')->get();
    //     } elseif ($user->isUnionManager()) {
    //         // Union Manager chỉ xem sự kiện của đoàn hội mình quản lý
    //         $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
    //         $events = Event::whereIn('union_id', $userUnionIds)->with('union')->get();
    //     } else {
    //         $events = collect();
    //     }

    //     $tabs = [
    //         'all' => Tab::make('Tất cả')
    //             ->badge($this->getModel()::count())
    //             ->badgeColor('primary'),
    //     ];

    //     // Tạo tab cho từng sự kiện
    //     foreach ($events as $event) {
    //         $attendanceCount = $this->getModel()::where('event_id', $event->id)->count();
            
    //         $tabs['event_' . $event->id] = Tab::make($event->title)
    //             ->badge($attendanceCount)
    //             ->badgeColor($attendanceCount > 0 ? 'success' : 'gray')
    //             ->modifyQueryUsing(function ($query) use ($event) {
    //                 return $query->where('event_id', $event->id);
    //             })
    //             ->badgeTooltip('Click để xuất Excel cho sự kiện này')
    //             ->badgeAction(
    //                 Action::make('export_event')
    //                     ->label('Xuất Excel')
    //                     ->icon('heroicon-o-arrow-down-tray')
    //                     ->color('success')
    //                     ->action(function () use ($event) {
    //                         $fileName = 'diem_danh_' . \Str::slug($event->title) . '_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                            
    //                         return Excel::download(new EventAttendanceExport($event->id), $fileName);
    //                     })
    //             );
    //     }

    //     return $tabs;
    // }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return parent::getTableQuery();
        }
        
        if ($user->isUnionManager()) {
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            return parent::getTableQuery()->whereHas('event', function ($query) use ($userUnionIds) {
                $query->whereIn('union_id', $userUnionIds);
            });
        }
        
        return parent::getTableQuery()->where('id', '<', 0); // Không có quyền
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AttendanceStatsWidget::class,
        ];
    }

    protected function getEventOptions(): array
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin có thể xem tất cả sự kiện
            $events = Event::with('union')->get();
        } elseif ($user->isUnionManager()) {
            // Union Manager chỉ xem sự kiện của đoàn hội mình quản lý
            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            $events = Event::whereIn('union_id', $userUnionIds)->with('union')->get();
        } else {
            $events = collect();
        }

        return $events->mapWithKeys(function ($event) {
            return [$event->id => $event->title . ' (' . $event->union->name . ')'];
        })->toArray();
    }
}

<?php

namespace App\Filament\Resources\EventComments\Widgets;

use App\Models\EventComment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParentCommentWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Bình luận gốc';

    public ?EventComment $parentComment = null;

    protected $listeners = ['refreshParentCommentWidget' => '$refresh'];

    protected function getStats(): array
    {
        $parentComment = $this->getParentComment();
        
        if (!$parentComment) {
            return [];
        }

        return [
            Stat::make('Người bình luận', $parentComment->user->full_name)
                ->description('Email: ' . $parentComment->user->email)
                ->descriptionIcon('heroicon-m-user')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'col-span-full',
                    'style' => 'height: 240px !important; min-height: 240px !important; display: flex !important; flex-direction: column !important;'
                ]),

            Stat::make('Sự kiện', $parentComment->event->title)
                ->description('Đoàn hội: ' . $parentComment->event->union->name)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->extraAttributes([
                    'class' => 'col-span-full',
                    'style' => 'height: 240px !important; min-height: 240px !important; display: flex !important; flex-direction: column !important;'
                ]),

            Stat::make('Thời gian', $parentComment->created_at->format('d/m/Y H:i'))
                ->description('Trạng thái: ' . ($parentComment->is_approved ? 'Đã duyệt' : 'Chờ duyệt'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($parentComment->is_approved ? 'success' : 'warning')
                ->extraAttributes([
                    'class' => 'col-span-full',
                    'style' => 'height: 240px !important; min-height: 240px !important; display: flex !important; flex-direction: column !important;'
                ]),

            Stat::make('Nội dung bình luận', '')
                ->description($parentComment->content)
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'col-span-full',
                    'style' => 'height: 240px !important; min-height: 240px !important; display: flex !important; flex-direction: column !important;'
                ]),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    private function getParentComment(): ?EventComment
    {
        // Nếu đã có parentComment, trả về
        if ($this->parentComment) {
            return $this->parentComment;
        }
        
        // Lấy từ livewire component
        $livewire = $this->getLivewire();
        if (method_exists($livewire, 'getRecord')) {
            $record = $livewire->getRecord();
            
            // Nếu đây là phản hồi, lấy bình luận gốc
            if ($record->parent_id) {
                return EventComment::find($record->parent_id);
            }
            
            // Nếu đây là bình luận gốc, trả về chính nó
            return $record;
        }
        
        return null;
    }
}

<?php

namespace App\Filament\Resources\EventComments\Pages;

use App\Filament\Resources\EventComments\EventCommentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEventComment extends ViewRecord
{
    protected static string $resource = EventCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            
            \Filament\Actions\Action::make('reply')
                ->label('Trả lời')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->visible(fn () => is_null($this->getRecord()->parent_id)) // Chỉ hiển thị cho bình luận chính
                ->form([
                    \Filament\Forms\Components\Textarea::make('reply_content')
                        ->label('Nội dung trả lời')
                        ->required()
                        ->rows(4)
                        ->maxLength(500)
                        ->placeholder('Viết phản hồi cho bình luận này...'),
                ])
                ->action(function (array $data) {
                    \App\Models\EventComment::create([
                        'event_id' => $this->getRecord()->event_id,
                        'user_id' => auth()->id(), // Admin/Manager trả lời
                        'parent_id' => $this->getRecord()->id,
                        'content' => $data['reply_content'],
                        'is_approved' => true,
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Phản hồi đã được thêm')
                        ->success()
                        ->send();
                })
                ->modalHeading('Trả lời bình luận')
                ->modalDescription('Viết phản hồi cho bình luận này')
                ->modalSubmitActionLabel('Gửi phản hồi'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\EventComments\Widgets\ParentCommentWidget::make([
                'parentComment' => $this->getParentComment(),
            ]),
            
            \App\Filament\Resources\EventComments\Widgets\CommentRepliesWidget::make([
                'parentComment' => $this->getParentComment(),
            ]),
        ];
    }

    private function getParentComment()
    {
        $record = $this->getRecord();
        
        // Nếu đây là phản hồi, lấy bình luận gốc
        if ($record->parent_id) {
            return \App\Models\EventComment::find($record->parent_id);
        }
        
        // Nếu đây là bình luận gốc, trả về chính nó
        return $record;
    }
}
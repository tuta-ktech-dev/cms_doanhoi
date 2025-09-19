<?php

namespace App\Filament\Resources\EventComments\Widgets;

use App\Models\EventComment;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;

class CommentRepliesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Phản hồi';

    public ?EventComment $parentComment = null;

    protected $listeners = ['refreshCommentRepliesWidget' => '$refresh'];

    public function table(Table $table): Table
    {
        $parentComment = $this->getParentComment();
        
        return $table
            ->query(
                EventComment::query()
                    ->where('parent_id', $parentComment->id)
                    ->orderBy('created_at', 'asc')
            )
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('Người trả lời')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('content')
                    ->label('Nội dung phản hồi')
                    ->html()
                    ->searchable(),

                BadgeColumn::make('is_approved')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Đã duyệt' : 'Chờ duyệt'),

                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Duyệt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => !$record->is_approved)
                        ->requiresConfirmation()
                        ->modalHeading('Duyệt phản hồi')
                        ->modalDescription('Bạn có chắc chắn muốn duyệt phản hồi này?')
                        ->action(function ($record) {
                            $record->update(['is_approved' => true]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Phản hồi đã được duyệt')
                                ->success()
                                ->send();
                            
                            // Refresh widget để hiển thị thay đổi
                            $this->dispatch('$refresh');
                        }),

                    Action::make('disapprove')
                        ->label('Hủy duyệt')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn ($record) => $record->is_approved)
                        ->requiresConfirmation()
                        ->modalHeading('Hủy duyệt phản hồi')
                        ->modalDescription('Bạn có chắc chắn muốn hủy duyệt phản hồi này?')
                        ->action(function ($record) {
                            $record->update(['is_approved' => false]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Phản hồi đã bị hủy duyệt')
                                ->warning()
                                ->send();
                            
                            // Refresh widget để hiển thị thay đổi
                            $this->dispatch('$refresh');
                        }),
                ])
            ])
            ->emptyStateHeading('Chưa có phản hồi')
            ->emptyStateDescription('Chưa có ai trả lời bình luận này.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    private function getParentComment(): EventComment
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
        
        // Fallback: tạo một comment mặc định
        return new EventComment();
    }
}

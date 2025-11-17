<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\FontWeight;

class EventCommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Bình luận sự kiện';

    protected static ?string $modelLabel = 'Bình luận';

    protected static ?string $pluralModelLabel = 'Bình luận';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('content')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('Ảnh đại diện')
                    ->formatStateUsing(function ($state) {
                        $initials = '';
                        $words = explode(' ', $state);
                        foreach ($words as $word) {
                            if (!empty($word)) {
                                $initials .= strtoupper(substr($word, 0, 1));
                            }
                        }
                        return substr($initials, 0, 2);
                    })
                    ->badge()
                    ->color('primary')
                    ->extraAttributes(['class' => 'w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold']),

                TextColumn::make('user.full_name')
                    ->label('Người bình luận')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->copyable()
                    ->copyMessage('Email đã được sao chép')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('content')
                    ->label('Nội dung bình luận')
                    ->html()
                    ->searchable()
                    ->limit(70)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen(strip_tags($state)) > 70 ? strip_tags($state) : null;
                    })
                    ->formatStateUsing(function ($state, $record) {
                        $content = $state;
                        if ($record->parent_id) {
                            // Nếu là phản hồi, thêm prefix
                            $parentComment = \App\Models\EventComment::find($record->parent_id);
                            if ($parentComment) {
                                $parentContent = strip_tags($parentComment->content);
                                $parentPreview = strlen($parentContent) > 50 ? substr($parentContent, 0, 50) . '...' : $parentContent;
                                return '<div class="border-l-4 border-blue-300 pl-3"><div class="text-xs text-gray-500 mb-1">↳ Trả lời: "' . $parentPreview . '"</div><div>' . $content . '</div></div>';
                            }
                        }
                        return $content;
                    }),

                BadgeColumn::make('is_approved')
                    ->label('Trạng thái duyệt')
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Đã duyệt' : 'Chờ duyệt'),

                BadgeColumn::make('parent_id')
                    ->label('Loại')
                    ->formatStateUsing(fn ($state): string => $state !== null ? 'Phản hồi' : 'Bình luận chính')
                    ->colors([
                        'primary' => fn ($state) => $state === null,
                        'secondary' => fn ($state) => $state !== null,
                    ])
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Ngày bình luận')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_approved')
                    ->label('Trạng thái duyệt')
                    ->options([
                        true => 'Đã duyệt',
                        false => 'Chờ duyệt',
                    ]),

                SelectFilter::make('parent_id')
                    ->label('Loại bình luận')
                    ->options([
                        null => 'Bình luận chính',
                        'has_replies' => 'Có phản hồi',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === null) {
                            return $query->whereNull('parent_id');
                        }
                        if ($data['value'] === 'has_replies') {
                            return $query->whereHas('replies');
                        }
                    }),
            ])
            ->headerActions([
                // Không cho phép tạo bình luận từ admin panel
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Xem chi tiết')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => route('filament.admin.resources.event-comments.view', $record))
                        ->openUrlInNewTab(),

                    Action::make('approve')
                        ->label('Duyệt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => !$record->is_approved)
                        ->requiresConfirmation()
                        ->modalHeading('Duyệt bình luận')
                        ->modalDescription('Bạn có chắc chắn muốn duyệt bình luận này?')
                        ->action(function ($record) {
                            $record->update(['is_approved' => true]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Bình luận đã được duyệt')
                                ->success()
                                ->send();
                        }),

                    Action::make('disapprove')
                        ->label('Hủy duyệt')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn ($record) => $record->is_approved)
                        ->requiresConfirmation()
                        ->modalHeading('Hủy duyệt bình luận')
                        ->modalDescription('Bạn có chắc chắn muốn hủy duyệt bình luận này?')
                        ->action(function ($record) {
                            $record->update(['is_approved' => false]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Bình luận đã bị hủy duyệt')
                                ->warning()
                                ->send();
                        }),
                ])
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

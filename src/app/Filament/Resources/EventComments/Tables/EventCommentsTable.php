<?php

namespace App\Filament\Resources\EventComments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class EventCommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email đã được sao chép')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('event.title')
                    ->label('Sự kiện')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),
                
                TextColumn::make('event.union.name')
                    ->label('Đoàn hội')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('content')
                    ->label('Nội dung bình luận')
                    ->limit(100)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 100 ? $state : null;
                    })
                    ->html()
                    ->searchable()
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
                    ->label('Trạng thái')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Đã duyệt' : 'Chờ duyệt')
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ]),
                
                TextColumn::make('parent_id')
                    ->label('Loại')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) {
                            return 'Bình luận chính';
                        }
                        return 'Phản hồi';
                    })
                    ->badge()
                    ->color(function ($state) {
                        return $state === null ? 'primary' : 'secondary';
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                
                TextColumn::make('created_at')
                    ->label('Ngày bình luận')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                TextColumn::make('updated_at')
                    ->label('Cập nhật lần cuối')
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
                
                SelectFilter::make('event_id')
                    ->label('Sự kiện')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->isAdmin()) {
                            return \App\Models\Event::all()->pluck('title', 'id');
                        }
                        if ($user->isUnionManager()) {
                            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
                            return \App\Models\Event::whereIn('union_id', $userUnionIds)->pluck('title', 'id');
                        }
                        return [];
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('union_id')
                    ->label('Đoàn hội')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->isAdmin()) {
                            return \App\Models\Union::all()->pluck('name', 'id');
                        }
                        if ($user->isUnionManager()) {
                            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
                            return \App\Models\Union::whereIn('id', $userUnionIds)->pluck('name', 'id');
                        }
                        return [];
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('user_id')
                    ->label('Người bình luận')
                    ->options(function () {
                        return \App\Models\User::where('role', 'student')
                            ->pluck('full_name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                
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
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem chi tiết'),
                    
                    EditAction::make()
                        ->label('Chỉnh sửa'),
                    
                    Action::make('reply')
                        ->label('Trả lời')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('info')
                        ->visible(fn ($record) => is_null($record->parent_id)) // Chỉ hiển thị cho bình luận chính
                        ->form([
                            \Filament\Forms\Components\Textarea::make('reply_content')
                                ->label('Nội dung trả lời')
                                ->required()
                                ->rows(3)
                                ->maxLength(500),
                        ])
                        ->action(function ($record, array $data) {
                            \App\Models\EventComment::create([
                                'event_id' => $record->event_id,
                                'user_id' => auth()->id(), // Admin/Manager trả lời
                                'parent_id' => $record->id,
                                'content' => $data['reply_content'],
                                'is_approved' => true,
                            ]);
                        })
                        ->modalHeading('Trả lời bình luận')
                        ->modalDescription('Viết phản hồi cho bình luận này'),
                    
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
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('bulk_approve')
                        ->label('Duyệt hàng loạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Duyệt hàng loạt')
                        ->modalDescription('Bạn có chắc chắn muốn duyệt tất cả bình luận đã chọn?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (!$record->is_approved) {
                                    $record->update(['is_approved' => true]);
                                }
                            });
                        }),
                    
                    Action::make('bulk_disapprove')
                        ->label('Hủy duyệt hàng loạt')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Hủy duyệt hàng loạt')
                        ->modalDescription('Bạn có chắc chắn muốn hủy duyệt tất cả bình luận đã chọn?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->is_approved) {
                                    $record->update(['is_approved' => false]);
                                }
                            });
                        }),
                    
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

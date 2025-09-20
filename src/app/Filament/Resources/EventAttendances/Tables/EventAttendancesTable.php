<?php

namespace App\Filament\Resources\EventAttendances\Tables;

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

class EventAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('Avatar')
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
                    ->label('Người tham gia')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->copyable()
                    ->copyMessage('Email đã được sao chép')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('event.title')
                    ->label('Sự kiện')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('event.union.name')
                    ->label('Đoàn hội')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'info' => 'excused',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Có mặt',
                        'absent' => 'Vắng mặt',
                        'late' => 'Đi muộn',
                        'excused' => 'Có phép',
                        default => $state,
                    }),

                TextColumn::make('attended_at')
                    ->label('Thời gian điểm danh')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('registeredBy.full_name')
                    ->label('Người điểm danh')
                    ->placeholder('Hệ thống')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Ghi chú')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state ? (strlen($state) > 30 ? $state : null) : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'present' => 'Có mặt',
                        'absent' => 'Vắng mặt',
                        'late' => 'Đi muộn',
                        'excused' => 'Có phép',
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
                    ->preload()
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            return $query->whereHas('event', function ($q) use ($data) {
                                $q->where('union_id', $data['value']);
                            });
                        }
                        return $query;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem chi tiết'),

                    EditAction::make()
                        ->label('Chỉnh sửa'),

                    Action::make('mark_present')
                        ->label('Đánh dấu có mặt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status !== 'present')
                        ->requiresConfirmation()
                        ->modalHeading('Đánh dấu có mặt')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu người này có mặt?')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'present',
                                'attended_at' => now(),
                                'registered_by' => auth()->id(),
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Đã đánh dấu có mặt')
                                ->success()
                                ->send();
                        }),

                    Action::make('mark_absent')
                        ->label('Đánh dấu vắng mặt')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status !== 'absent')
                        ->requiresConfirmation()
                        ->modalHeading('Đánh dấu vắng mặt')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu người này vắng mặt?')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'absent',
                                'attended_at' => now(),
                                'registered_by' => auth()->id(),
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Đã đánh dấu vắng mặt')
                                ->warning()
                                ->send();
                        }),

                    Action::make('mark_late')
                        ->label('Đánh dấu đi muộn')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->visible(fn ($record) => $record->status !== 'late')
                        ->requiresConfirmation()
                        ->modalHeading('Đánh dấu đi muộn')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu người này đi muộn?')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'late',
                                'attended_at' => now(),
                                'registered_by' => auth()->id(),
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Đã đánh dấu đi muộn')
                                ->warning()
                                ->send();
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('bulk_mark_present')
                        ->label('Đánh dấu có mặt hàng loạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Đánh dấu có mặt hàng loạt')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu tất cả người đã chọn có mặt?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'present',
                                    'attended_at' => now(),
                                    'registered_by' => auth()->id(),
                                ]);
                            });
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Đã đánh dấu có mặt hàng loạt')
                                ->success()
                                ->send();
                        }),

                    Action::make('bulk_mark_absent')
                        ->label('Đánh dấu vắng mặt hàng loạt')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Đánh dấu vắng mặt hàng loạt')
                        ->modalDescription('Bạn có chắc chắn muốn đánh dấu tất cả người đã chọn vắng mặt?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'absent',
                                    'attended_at' => now(),
                                    'registered_by' => auth()->id(),
                                ]);
                            });
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Đã đánh dấu vắng mặt hàng loạt')
                                ->warning()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('attended_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s') // Auto refresh every 30 seconds
            ->deferLoading() // Lazy load for better performance
            ->persistFiltersInSession() // Remember filters
            ->persistSortInSession() // Remember sort
            ->persistSearchInSession(); // Remember search
    }
}
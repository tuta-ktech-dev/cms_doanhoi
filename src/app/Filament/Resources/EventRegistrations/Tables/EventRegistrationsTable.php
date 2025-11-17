<?php

namespace App\Filament\Resources\EventRegistrations\Tables;

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

class EventRegistrationsTable
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
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email đã được sao chép')
                    ->copyMessageDuration(1500),
                
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
                    ->color('info'),
                
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                        default => $state,
                    }),
                
                TextColumn::make('registered_at')
                    ->label('Ngày đăng ký')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                TextColumn::make('approved_at')
                    ->label('Ngày duyệt')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Chưa duyệt'),
                
                TextColumn::make('approver.full_name')
                    ->label('Người duyệt')
                    ->placeholder('Chưa có')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('notes')
                    ->label('Ghi chú')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state ? (strlen($state) > 30 ? $state : null) : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
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
                    ->label('Sinh viên đăng ký')
                    ->options(function () {
                        return \App\Models\User::where('role', 'student')
                            ->pluck('full_name', 'id');
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem chi tiết'),
                    
                    EditAction::make()
                        ->label('Chỉnh sửa'),
                    
                    Action::make('approve')
                        ->label('Duyệt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Duyệt đăng ký')
                        ->modalDescription('Bạn có chắc chắn muốn duyệt đăng ký này?')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                                'approved_by' => auth()->id(),
                            ]);
                        }),
                    
                    Action::make('reject')
                        ->label('Từ chối')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Từ chối đăng ký')
                        ->modalDescription('Bạn có chắc chắn muốn từ chối đăng ký này?')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'rejected',
                                'approved_at' => now(),
                                'approved_by' => auth()->id(),
                            ]);
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
                        ->modalDescription('Bạn có chắc chắn muốn duyệt tất cả đăng ký đã chọn?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'approved',
                                        'approved_at' => now(),
                                        'approved_by' => auth()->id(),
                                    ]);
                                }
                            });
                        }),
                    
                    Action::make('bulk_reject')
                        ->label('Từ chối hàng loạt')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Từ chối hàng loạt')
                        ->modalDescription('Bạn có chắc chắn muốn từ chối tất cả đăng ký đã chọn?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'rejected',
                                        'approved_at' => now(),
                                        'approved_by' => auth()->id(),
                                    ]);
                                }
                            });
                        }),
                    
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('registered_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

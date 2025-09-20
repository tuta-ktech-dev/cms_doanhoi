<?php

namespace App\Filament\Resources\Unions\Tables;

use App\Models\Union;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class UnionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên đoàn hội')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('events_count')
                    ->label('Sự kiện')
                    ->counts('events')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('event_registrations_count')
                    ->label('Đăng ký')
                    ->counts('eventRegistrations')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('event_attendances_count')
                    ->label('Tham gia')
                    ->counts('eventAttendances')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('union_managers_count')
                    ->label('Quản lý')
                    ->counts('unionManagers')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('total_activity_points')
                    ->label('Tổng điểm DRL')
                    ->getStateUsing(function ($record) {
                        return $record->eventAttendances()
                            ->where('event_attendance.status', 'present')
                            ->with('event')
                            ->get()
                            ->sum(function ($attendance) {
                                return $attendance->event->activity_points ?? 0;
                            });
                    })
                    ->formatStateUsing(fn ($state) => $state . ' điểm')
                    ->sortable(false),

                TextColumn::make('avg_attendance_rate')
                    ->label('Tỷ lệ tham gia')
                    ->getStateUsing(function ($record) {
                        $totalRegistrations = $record->eventRegistrations()->where('event_registrations.status', 'approved')->count();
                        $totalAttendance = $record->eventAttendances()->count();
                        return $totalRegistrations > 0 ? round(($totalAttendance / $totalRegistrations) * 100, 1) : 0;
                    })
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(false),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('has_events')
                    ->label('Có sự kiện')
                    ->options([
                        'yes' => 'Có sự kiện',
                        'no' => 'Không có sự kiện',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'yes') {
                            return $query->has('events');
                        } elseif ($data['value'] === 'no') {
                            return $query->doesntHave('events');
                        }
                        return $query;
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
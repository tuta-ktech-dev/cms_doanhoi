<?php

namespace App\Filament\Resources\Students\Tables;

use App\Models\Student;
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

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_id')
                    ->label('MSSV')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('user.full_name')
                    ->label('Họ và tên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('class')
                    ->label('Lớp')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('faculty')
                    ->label('Khoa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('course')
                    ->label('Khóa')
                    ->sortable(),

                BadgeColumn::make('gender')
                    ->label('Giới tính')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Nam',
                        'female' => 'Nữ',
                        default => 'Khác',
                    })
                    ->colors([
                        'primary' => 'male',
                        'secondary' => 'female',
                    ]),

                TextColumn::make('date_of_birth')
                    ->label('Ngày sinh')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('activity_points_from_events')
                    ->label('Điểm rèn luyện')
                    ->getStateUsing(function ($record) {
                        return $record->eventAttendances()
                            ->where('status', 'present')
                            ->with('event')
                            ->get()
                            ->sum(function ($attendance) {
                                return $attendance->event->activity_points ?? 0;
                            });
                    })
                    ->formatStateUsing(fn ($state) => $state . ' điểm')
                    ->sortable(false), // Tắt sort vì không có cột thực tế

                TextColumn::make('event_registrations_count')
                    ->label('Đăng ký SK')
                    ->counts('eventRegistrations')
                    ->sortable(),

                TextColumn::make('event_attendances_count')
                    ->label('Tham gia SK')
                    ->counts('eventAttendances')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('faculty')
                    ->label('Khoa')
                    ->options([
                        'CNTT' => 'Công nghệ thông tin',
                        'KT' => 'Kinh tế',
                        'NN' => 'Ngoại ngữ',
                        'KH' => 'Khoa học',
                        'KHXH' => 'Khoa học xã hội',
                    ])
                    ->searchable(),

                SelectFilter::make('course')
                    ->label('Khóa')
                    ->options([
                        '2020' => 'Khóa 2020',
                        '2021' => 'Khóa 2021',
                        '2022' => 'Khóa 2022',
                        '2023' => 'Khóa 2023',
                        '2024' => 'Khóa 2024',
                    ]),

                SelectFilter::make('gender')
                    ->label('Giới tính')
                    ->options([
                        'male' => 'Nam',
                        'female' => 'Nữ',
                    ]),
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
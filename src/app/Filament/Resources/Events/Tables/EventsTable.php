<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Hình ảnh')
                    ->disk('public')
                    ->height(60)
                    ->defaultImageUrl('https://placehold.co/800x400/edeff5/4a5568?text=Event'),

                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('union.name')
                    ->label('Đoàn hội')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Địa điểm')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('max_participants')
                    ->label('Số lượng tối đa')
                    ->numeric()
                    ->toggleable(),

                TextColumn::make('activity_points')
                    ->label('Điểm rèn luyện')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('budget')
                    ->label('Kinh phí')
                    ->money('VND')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_registration_open')
                    ->label('Mở đăng ký')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Bản nháp',
                        'published' => 'Đã xuất bản',
                        'cancelled' => 'Đã hủy',
                    }),

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
                        'draft' => 'Bản nháp',
                        'published' => 'Đã xuất bản',
                        'cancelled' => 'Đã hủy',
                    ]),

                SelectFilter::make('is_registration_open')
                    ->label('Mở đăng ký')
                    ->options([
                        true => 'Có',
                        false => 'Không',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->label('Xem chi tiết'),

                    \Filament\Actions\EditAction::make()
                        ->label('Chỉnh sửa'),
                ])
            ])
            ->defaultSort('start_date', 'desc');
    }
}

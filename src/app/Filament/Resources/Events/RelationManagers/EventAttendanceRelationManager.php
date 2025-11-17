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
use App\Filament\Resources\Events\RelationManagers\EventAttendanceStatsWidget;
use App\Models\EventAttendance;

class EventAttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'attendance';

    protected static ?string $title = 'Điểm danh';

    protected static ?string $modelLabel = 'Điểm danh';

    protected static ?string $pluralModelLabel = 'Điểm danh';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Người tham gia')
                    ->relationship('user', 'full_name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\DateTimePicker::make('attended_at')
                    ->label('Thời gian điểm danh')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y H:i'),

                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'present' => 'Có mặt',
                        'absent' => 'Vắng mặt',
                        'late' => 'Đi muộn',
                        'excused' => 'Có phép',
                    ])
                    ->required()
                    ->default('present'),

                Forms\Components\Textarea::make('notes')
                    ->label('Ghi chú')
                    ->maxLength(500)
                    ->rows(3)
                    ->placeholder('Ghi chú về việc điểm danh...'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.full_name')
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
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Thêm điểm danh')
                    ->label('Thêm điểm danh')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['registered_by'] = auth()->id();
                        return $data;
                    }),

                Action::make('bulk_create_attendance')
                    ->label('Tạo điểm danh từ đăng ký')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Tạo điểm danh từ danh sách đăng ký')
                    ->modalDescription('Tạo điểm danh cho tất cả người đã đăng ký và được duyệt sự kiện này.')
                    ->action(function () {
                        $event = $this->getOwnerRecord();
                        $approvedRegistrations = $event->registrations()
                            ->where('status', 'approved')
                            ->with('user')
                            ->get();

                        $createdCount = 0;
                        foreach ($approvedRegistrations as $registration) {
                            // Kiểm tra xem đã có điểm danh chưa
                            $existingAttendance = EventAttendance::where('event_id', $event->id)
                                ->where('user_id', $registration->user_id)
                                ->first();

                            if (!$existingAttendance) {
                                EventAttendance::create([
                                    'event_id' => $event->id,
                                    'user_id' => $registration->user_id,
                                    'registered_by' => auth()->id(),
                                    'attended_at' => now(),
                                    'status' => 'absent', // Mặc định vắng mặt, admin sẽ cập nhật sau
                                    'notes' => 'Tạo tự động từ đăng ký',
                                ]);
                                $createdCount++;
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Đã tạo ' . $createdCount . ' điểm danh mới')
                            ->success()
                            ->send();
                    }),

                Action::make('export_excel')
                    ->label('Xuất Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $event = $this->getOwnerRecord();
                        $fileName = 'diem_danh_' . \Str::slug($event->title) . '_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
                        
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\EventAttendanceExport($event->id), 
                            $fileName
                        );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    \Filament\Actions\EditAction::make()
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

                    \Filament\Actions\DeleteAction::make()
                        ->label('Xóa'),
                ])
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
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

                    \Filament\Actions\DeleteBulkAction::make(),
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

    protected function getHeaderWidgets(): array
    {
        return [
            EventAttendanceStatsWidget::make([
                'eventId' => $this->getOwnerRecord()->id,
            ]),
        ];
    }
}

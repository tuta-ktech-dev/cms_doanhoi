# Tích hợp Quản lý Đăng ký với Filament

## Model Eloquent cho Đăng ký

### Model EventRegistration

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'student_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'registration_time' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs()
    {
        return $this->hasMany(RegistrationLog::class, 'registration_id');
    }

    public function notifications()
    {
        return $this->hasMany(RegistrationNotification::class, 'registration_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function getStudentNameAttribute()
    {
        return $this->student->user->full_name ?? 'N/A';
    }

    public function getStudentCodeAttribute()
    {
        return $this->student->student_id ?? 'N/A';
    }

    public function getEventTitleAttribute()
    {
        return $this->event->title ?? 'N/A';
    }
}
```

### Model RegistrationLog

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'notes',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(EventRegistration::class, 'registration_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## RelationManager cho Đăng ký trong EventResource

```php
<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\EventRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Danh sách đăng ký';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Sinh viên')
                    ->relationship('student.user', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Đang chờ',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Lý do từ chối')
                    ->maxLength(65535)
                    ->visible(fn (callable $get) => $get('status') === 'rejected'),
                Forms\Components\Textarea::make('notes')
                    ->label('Ghi chú')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.user.full_name')
                    ->label('Họ và tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.student_id')
                    ->label('MSSV')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.faculty')
                    ->label('Khoa/Ngành')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('registration_time')
                    ->label('Thời gian đăng ký')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Thời gian duyệt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approver.full_name')
                    ->label('Người duyệt')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Đang chờ',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (EventRegistration $record) {
                        // Ghi log
                        $record->logs()->create([
                            'user_id' => auth()->id(),
                            'action' => 'create',
                            'new_status' => $record->status,
                            'notes' => 'Tạo đăng ký mới',
                        ]);
                    }),
                Tables\Actions\Action::make('exportExcel')
                    ->label('Xuất Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        // Logic xuất Excel
                    }),
                Tables\Actions\Action::make('approveAll')
                    ->label('Duyệt tất cả đang chờ')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function () {
                        DB::transaction(function () {
                            $pendingRegistrations = $this->getOwnerRecord()->registrations()->pending()->get();
                            
                            foreach ($pendingRegistrations as $registration) {
                                $registration->update([
                                    'status' => 'approved',
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                ]);
                                
                                // Ghi log
                                $registration->logs()->create([
                                    'user_id' => auth()->id(),
                                    'action' => 'update_status',
                                    'old_status' => 'pending',
                                    'new_status' => 'approved',
                                    'notes' => 'Duyệt hàng loạt',
                                ]);
                            }
                        });
                    })
                    ->requiresConfirmation(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (EventRegistration $record, array $data) {
                        // Ghi log nếu trạng thái thay đổi
                        if ($record->wasChanged('status')) {
                            $record->logs()->create([
                                'user_id' => auth()->id(),
                                'action' => 'update_status',
                                'old_status' => $record->getOriginal('status'),
                                'new_status' => $record->status,
                                'notes' => $data['notes'] ?? null,
                            ]);
                        }
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function (EventRegistration $record) {
                        DB::transaction(function () use ($record) {
                            $oldStatus = $record->status;
                            
                            $record->update([
                                'status' => 'approved',
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);
                            
                            // Ghi log
                            $record->logs()->create([
                                'user_id' => auth()->id(),
                                'action' => 'update_status',
                                'old_status' => $oldStatus,
                                'new_status' => 'approved',
                                'notes' => 'Duyệt đăng ký',
                            ]);
                        });
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Lý do từ chối')
                            ->required(),
                    ])
                    ->action(function (EventRegistration $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $oldStatus = $record->status;
                            
                            $record->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);
                            
                            // Ghi log
                            $record->logs()->create([
                                'user_id' => auth()->id(),
                                'action' => 'update_status',
                                'old_status' => $oldStatus,
                                'new_status' => 'rejected',
                                'notes' => 'Từ chối: ' . $data['rejection_reason'],
                            ]);
                        });
                    }),
                Tables\Actions\Action::make('viewLogs')
                    ->label('Xem lịch sử')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->action(function () {
                        // Mở modal hiển thị lịch sử
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function (EventRegistration $record) {
                        // Ghi log
                        $record->logs()->create([
                            'user_id' => auth()->id(),
                            'action' => 'delete',
                            'old_status' => $record->status,
                            'notes' => 'Xóa đăng ký',
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approveSelected')
                        ->label('Duyệt đã chọn')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            DB::transaction(function () use ($records) {
                                $records->each(function ($record) {
                                    if ($record->status === 'pending') {
                                        $oldStatus = $record->status;
                                        
                                        $record->update([
                                            'status' => 'approved',
                                            'approved_by' => auth()->id(),
                                            'approved_at' => now(),
                                        ]);
                                        
                                        // Ghi log
                                        $record->logs()->create([
                                            'user_id' => auth()->id(),
                                            'action' => 'update_status',
                                            'old_status' => $oldStatus,
                                            'new_status' => 'approved',
                                            'notes' => 'Duyệt hàng loạt',
                                        ]);
                                    }
                                });
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('rejectSelected')
                        ->label('Từ chối đã chọn')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Lý do từ chối')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            DB::transaction(function () use ($records, $data) {
                                $records->each(function ($record) use ($data) {
                                    if ($record->status === 'pending') {
                                        $oldStatus = $record->status;
                                        
                                        $record->update([
                                            'status' => 'rejected',
                                            'rejection_reason' => $data['rejection_reason'],
                                            'approved_by' => auth()->id(),
                                            'approved_at' => now(),
                                        ]);
                                        
                                        // Ghi log
                                        $record->logs()->create([
                                            'user_id' => auth()->id(),
                                            'action' => 'update_status',
                                            'old_status' => $oldStatus,
                                            'new_status' => 'rejected',
                                            'notes' => 'Từ chối hàng loạt: ' . $data['rejection_reason'],
                                        ]);
                                    }
                                });
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

## Tạo RegistrationResource độc lập

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Filament\Resources\RegistrationResource\RelationManagers;
use App\Models\EventRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Quản lý sự kiện';

    protected static ?string $navigationLabel = 'Quản lý đăng ký';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label('Sự kiện')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->label('Sinh viên')
                    ->relationship('student.user', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Đang chờ',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Lý do từ chối')
                    ->maxLength(65535)
                    ->visible(fn (callable $get) => $get('status') === 'rejected'),
                Forms\Components\Textarea::make('notes')
                    ->label('Ghi chú')
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Sự kiện')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.user.full_name')
                    ->label('Họ và tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.student_id')
                    ->label('MSSV')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('registration_time')
                    ->label('Thời gian đăng ký')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Thời gian duyệt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Sự kiện')
                    ->relationship('event', 'title'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Đang chờ',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
            'view' => Pages\ViewRegistration::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (auth()->user()->role === 'admin') {
            return $query; // Admin thấy tất cả
        }
        
        if (auth()->user()->role === 'union_manager') {
            $unionId = auth()->user()->unionManager->union_id;
            return $query->whereHas('event', function ($query) use ($unionId) {
                $query->where('union_id', $unionId);
            });
        }
        
        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
```

## Widget cho Dashboard

```php
<?php

namespace App\Filament\Widgets;

use App\Models\EventRegistration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RegistrationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Đăng ký đang chờ', EventRegistration::where('status', 'pending')->count())
                ->description('Cần xử lý')
                ->color('warning'),
            
            Stat::make('Đăng ký đã duyệt', EventRegistration::where('status', 'approved')->count())
                ->description('Đã xác nhận tham gia')
                ->color('success'),
            
            Stat::make('Đăng ký từ chối', EventRegistration::where('status', 'rejected')->count())
                ->description('Không đủ điều kiện')
                ->color('danger'),
        ];
    }
}
```

## Phân quyền trong Filament

Để giới hạn quyền quản lý đăng ký theo vai trò:

```php
// Trong database seeder hoặc migration
$adminRole = \App\Models\Role::where('name', 'admin')->first();
$adminRole->givePermissionTo([
    'view_any_eventregistration',
    'view_eventregistration',
    'create_eventregistration',
    'update_eventregistration',
    'delete_eventregistration',
]);

$unionManagerRole = \App\Models\Role::where('name', 'union_manager')->first();
$unionManagerRole->givePermissionTo([
    'view_any_eventregistration',
    'view_eventregistration',
    'create_eventregistration',
    'update_eventregistration',
    'delete_eventregistration',
]);
```

## Lưu ý khi tích hợp

1. **Ghi log hoạt động**: Luôn ghi log khi có thay đổi trạng thái đăng ký để dễ dàng theo dõi và kiểm tra.

2. **Giao dịch database**: Sử dụng DB::transaction để đảm bảo tính toàn vẹn dữ liệu khi thực hiện nhiều thao tác liên quan.

3. **Phân quyền**: Đảm bảo người quản lý đoàn hội chỉ có thể xem và quản lý đăng ký cho sự kiện của đoàn hội mình.

4. **Thông báo**: Tích hợp hệ thống thông báo để gửi thông tin đến sinh viên khi trạng thái đăng ký thay đổi.

5. **Xuất báo cáo**: Cung cấp chức năng xuất báo cáo đăng ký ra file Excel/CSV để dễ dàng quản lý và điểm danh.

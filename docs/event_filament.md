# Tích hợp Quản lý Sự kiện với Filament

## Model Eloquent cho Sự kiện

### Model Event

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'start_time',
        'end_time',
        'location',
        'union_id',
        'created_by',
        'activity_points',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'activity_points' => 'decimal:2',
    ];

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendances()
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getRegisteredCountAttribute()
    {
        return $this->registrations()->where('status', 'approved')->count();
    }

    public function getAttendedCountAttribute()
    {
        return $this->attendances()->whereNotNull('check_in_time')->count();
    }
}
```

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
}
```

## Filament Resource cho Sự kiện

### EventResource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\Union;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Quản lý sự kiện';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255),

                Forms\Components\RichEditor::make('content')
                    ->label('Nội dung')
                    ->required()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('events/content')
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('start_time')
                    ->label('Thời gian bắt đầu')
                    ->required(),

                Forms\Components\DateTimePicker::make('end_time')
                    ->label('Thời gian kết thúc')
                    ->required()
                    ->after('start_time'),

                Forms\Components\TextInput::make('location')
                    ->label('Địa điểm')
                    ->maxLength(255),

                Forms\Components\Select::make('union_id')
                    ->label('Đoàn hội tổ chức')
                    ->options(Union::where('status', 'active')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('activity_points')
                    ->label('Điểm rèn luyện')
                    ->numeric()
                    ->default(0)
                    ->step(0.5),

                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'upcoming' => 'Sắp diễn ra',
                        'ongoing' => 'Đang diễn ra',
                        'completed' => 'Đã kết thúc',
                        'cancelled' => 'Đã hủy',
                    ])
                    ->default('upcoming')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('union.name')
                    ->label('Đoàn hội')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Bắt đầu')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Kết thúc')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('registered_count')
                    ->label('Đăng ký')
                    ->counts('registrations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_points')
                    ->label('Điểm RL')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'info',
                        'ongoing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'upcoming' => 'Sắp diễn ra',
                        'ongoing' => 'Đang diễn ra',
                        'completed' => 'Đã kết thúc',
                        'cancelled' => 'Đã hủy',
                    ]),
                Tables\Filters\SelectFilter::make('union_id')
                    ->label('Đoàn hội')
                    ->relationship('union', 'name'),
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
            RelationManagers\RegistrationsRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'view' => Pages\ViewEvent::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'upcoming')->count();
    }
}
```

### RelationManager cho EventRegistrations

```php
<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ])
                    ->default('pending')
                    ->required(),
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
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('registration_time')
                    ->label('Thời gian đăng ký')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Đang chờ',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('exportExcel')
                    ->label('Xuất Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        // Export logic
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

### RelationManager cho EventComments

```php
<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Bình luận';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Người dùng')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('parent_id')
                    ->label('Trả lời cho')
                    ->relationship('parent', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Bình luận #{$record->id}")
                    ->searchable(),
                Forms\Components\Textarea::make('content')
                    ->label('Nội dung')
                    ->required()
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Người dùng')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Nội dung')
                    ->limit(50),
                Tables\Columns\TextColumn::make('parent_id')
                    ->label('Trả lời')
                    ->formatStateUsing(fn ($state) => $state ? "Bình luận #{$state}" : '-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

## Phân quyền trong Filament

Để giới hạn quản lý đoàn hội chỉ thấy và quản lý sự kiện của đoàn hội mình:

```php
// Trong EventResource.php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    
    if (auth()->user()->role === 'admin') {
        return $query; // Admin thấy tất cả
    }
    
    if (auth()->user()->role === 'union_manager') {
        $unionId = auth()->user()->unionManager->union_id;
        return $query->where('union_id', $unionId);
    }
    
    return $query;
}
```

## Widget cho Dashboard

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Sự kiện sắp tới', Event::where('status', 'upcoming')->count())
                ->description('Số sự kiện sắp diễn ra')
                ->color('info'),
            
            Stat::make('Sự kiện đang diễn ra', Event::where('status', 'ongoing')->count())
                ->description('Số sự kiện đang diễn ra')
                ->color('warning'),
            
            Stat::make('Tổng số đăng ký', function () {
                return \App\Models\EventRegistration::where('status', 'approved')->count();
            })
                ->description('Đăng ký đã duyệt')
                ->color('success'),
        ];
    }
}
```

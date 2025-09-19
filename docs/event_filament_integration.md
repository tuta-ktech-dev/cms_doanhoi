# Tích hợp Quản lý Sự kiện với Filament

## Tạo Model Eloquent cho Sự kiện

### Model Event

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'content',
        'banner_image',
        'start_time',
        'end_time',
        'location',
        'max_participants',
        'registration_deadline',
        'activity_points',
        'union_id',
        'created_by',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'registration_deadline' => 'datetime',
        'activity_points' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('title') && !$event->isDirty('slug')) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categories()
    {
        return $this->belongsToMany(EventCategory::class, 'event_category_relationships', 'event_id', 'category_id');
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

    public function attachments()
    {
        return $this->hasMany(EventAttachment::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'event_likes', 'event_id', 'user_id');
    }

    public function feedback()
    {
        return $this->hasMany(EventFeedback::class);
    }

    public function notifications()
    {
        return $this->hasMany(EventNotification::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(EventActivityLog::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
                     ->where('status', 'published');
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_time', '<=', now())
                     ->where('end_time', '>=', now())
                     ->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByUnion($query, $unionId)
    {
        return $query->where('union_id', $unionId);
    }

    public function getRegisteredCountAttribute()
    {
        return $this->registrations()->where('status', 'approved')->count();
    }

    public function getAttendedCountAttribute()
    {
        return $this->attendances()->whereNotNull('check_in_time')->count();
    }

    public function getIsRegistrationOpenAttribute()
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->registration_deadline && now() > $this->registration_deadline) {
            return false;
        }

        if ($this->max_participants && $this->registered_count >= $this->max_participants) {
            return false;
        }

        return true;
    }
}
```

### Model EventCategory

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_category_relationships', 'category_id', 'event_id');
    }
}
```

## Tạo Filament Resources

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
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Quản lý sự kiện';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin cơ bản')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('short_description')
                            ->label('Mô tả ngắn')
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\RichEditor::make('content')
                            ->label('Nội dung')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('events/content')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('banner_image')
                            ->label('Ảnh bìa')
                            ->image()
                            ->disk('public')
                            ->directory('events/banners')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Thời gian và địa điểm')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('Thời gian bắt đầu')
                            ->required(),

                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('Thời gian kết thúc')
                            ->required()
                            ->after('start_time'),

                        Forms\Components\TextInput::make('location')
                            ->label('Địa điểm')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Đăng ký và điểm')
                    ->schema([
                        Forms\Components\TextInput::make('max_participants')
                            ->label('Số lượng tham gia tối đa')
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\DateTimePicker::make('registration_deadline')
                            ->label('Hạn đăng ký')
                            ->before('start_time'),

                        Forms\Components\TextInput::make('activity_points')
                            ->label('Điểm rèn luyện')
                            ->numeric()
                            ->default(0)
                            ->step(0.5),
                    ]),

                Forms\Components\Section::make('Phân loại')
                    ->schema([
                        Forms\Components\Select::make('union_id')
                            ->label('Đoàn hội tổ chức')
                            ->options(Union::where('status', 'active')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('categories')
                            ->label('Danh mục')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên danh mục')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\Textarea::make('description')
                                    ->label('Mô tả')
                                    ->maxLength(255),
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Màu sắc'),
                            ]),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Sự kiện nổi bật')
                            ->default(false),
                    ]),

                Forms\Components\Section::make('Trạng thái')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'draft' => 'Bản nháp',
                                'published' => 'Đã công bố',
                                'ongoing' => 'Đang diễn ra',
                                'completed' => 'Đã kết thúc',
                                'cancelled' => 'Đã hủy',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner_image')
                    ->label('Ảnh bìa')
                    ->circular(),
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
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Nổi bật')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'ongoing' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'draft' => 'Bản nháp',
                        'published' => 'Đã công bố',
                        'ongoing' => 'Đang diễn ra',
                        'completed' => 'Đã kết thúc',
                        'cancelled' => 'Đã hủy',
                    ]),
                Tables\Filters\SelectFilter::make('union_id')
                    ->label('Đoàn hội')
                    ->relationship('union', 'name'),
                Tables\Filters\Filter::make('is_featured')
                    ->label('Sự kiện nổi bật')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true)),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Sắp diễn ra')
                    ->query(fn (Builder $query): Builder => $query->where('start_time', '>', now())),
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
            RelationManagers\AttendancesRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\AttachmentsRelationManager::class,
            RelationManagers\FeedbackRelationManager::class,
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
        return static::getModel()::where('status', 'published')->count();
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
use Illuminate\Database\Eloquent\Builder;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $recordTitleAttribute = 'id';

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
                Forms\Components\Textarea::make('notes')
                    ->label('Ghi chú')
                    ->maxLength(65535)
                    ->columnSpanFull(),
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
                    ->sortable(),
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
                    Tables\Actions\BulkAction::make('approveSelected')
                        ->label('Duyệt đã chọn')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'approved',
                                        'approved_by' => auth()->id(),
                                        'approved_at' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('rejectSelected')
                        ->label('Từ chối đã chọn')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'rejected',
                                        'approved_by' => auth()->id(),
                                        'approved_at' => now(),
                                    ]);
                                }
                            });
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

## Widget cho Dashboard

### EventStatsWidget

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
            Stat::make('Sự kiện đang diễn ra', Event::where('status', 'ongoing')->count())
                ->description('Số sự kiện đang diễn ra')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            
            Stat::make('Sự kiện sắp tới', Event::upcoming()->count())
                ->description('Số sự kiện sắp diễn ra')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
            
            Stat::make('Tổng số đăng ký', function () {
                $upcomingEvents = Event::upcoming()->pluck('id');
                return \App\Models\EventRegistration::whereIn('event_id', $upcomingEvents)
                    ->where('status', 'approved')
                    ->count();
            })
                ->description('Đăng ký cho sự kiện sắp tới')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
```

### UpcomingEventsWidget

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->upcoming()
                    ->orderBy('start_time')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('union.name')
                    ->label('Đoàn hội')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Bắt đầu')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('registered_count')
                    ->label('Đăng ký')
                    ->counts('registrations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_participants')
                    ->label('Tối đa')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Xem')
                    ->url(fn (Event $record): string => route('filament.admin.resources.events.view', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
```

## Tích hợp Plugin Filament Shield cho phân quyền

Để quản lý quyền hạn trong quản lý sự kiện, bạn có thể sử dụng plugin Filament Shield:

```bash
composer require bezhansalleh/filament-shield
php artisan shield:install
```

Sau đó, tạo các quyền cho quản lý sự kiện:

```bash
php artisan shield:generate --resource=Event
```

Cấu hình các quyền cho từng vai trò:

```php
// Trong database seeder hoặc migration
$adminRole = \App\Models\Role::where('name', 'admin')->first();
$adminRole->givePermissionTo([
    'view_any_event',
    'view_event',
    'create_event',
    'update_event',
    'delete_event',
    'manage_event_registrations',
    'manage_event_attendances',
]);

$unionManagerRole = \App\Models\Role::where('name', 'union_manager')->first();
$unionManagerRole->givePermissionTo([
    'view_any_event',
    'view_event',
    'create_event',
    'update_event',
    'delete_event',
    'manage_event_registrations',
    'manage_event_attendances',
]);
```

## Bảo mật và Giới hạn truy cập

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

## Tùy chỉnh giao diện

### Thêm Action tùy chỉnh

```php
// Trong EventResource.php
public static function getActions(): array
{
    return [
        Actions\Action::make('generateQRCode')
            ->label('Tạo mã QR điểm danh')
            ->icon('heroicon-o-qr-code')
            ->action(function (Event $record) {
                // Logic tạo mã QR
            }),
        Actions\Action::make('sendNotification')
            ->label('Gửi thông báo')
            ->icon('heroicon-o-bell')
            ->form([
                Forms\Components\TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('Nội dung')
                    ->required(),
                Forms\Components\Select::make('notification_type')
                    ->label('Loại thông báo')
                    ->options([
                        'reminder' => 'Nhắc nhở',
                        'update' => 'Cập nhật',
                        'cancellation' => 'Hủy',
                        'other' => 'Khác',
                    ])
                    ->default('reminder')
                    ->required(),
            ])
            ->action(function (array $data, Event $record) {
                // Logic gửi thông báo
            }),
    ];
}
```

## Lưu ý khi tích hợp

1. **Quản lý file**: Sử dụng disk và directory phù hợp cho việc lưu trữ hình ảnh và tài liệu đính kèm.

2. **Rich Editor**: Sử dụng Filament RichEditor để hỗ trợ nội dung HTML cho sự kiện.

3. **Phân quyền**: Cấu hình phân quyền để đảm bảo người dùng chỉ thấy và quản lý sự kiện của đoàn hội mình.

4. **Tùy chỉnh form**: Tùy chỉnh form để dễ dàng nhập liệu và quản lý sự kiện.

5. **Tùy chỉnh bảng**: Tùy chỉnh bảng để hiển thị thông tin quan trọng và các action phù hợp.

6. **Widget**: Sử dụng widget để hiển thị thông tin tổng quan về sự kiện trên dashboard.

7. **Export**: Tích hợp chức năng xuất báo cáo dưới dạng Excel hoặc PDF.

# Tích hợp với Filament Admin Panel

## Giới thiệu

[Filament](https://filamentphp.com/) là một admin panel framework cho Laravel, giúp xây dựng giao diện quản trị nhanh chóng và đẹp mắt. Tài liệu này mô tả cách tích hợp cấu trúc database của hệ thống CMS Đoàn Hội với Filament.

## Cài đặt Filament

```bash
composer require filament/filament
php artisan filament:install --panels
```

## Tạo Model Eloquent

Tạo các model tương ứng với các bảng trong database:

```bash
php artisan make:model User -m
php artisan make:model Student -m
php artisan make:model Union -m
php artisan make:model UnionManager -m
php artisan make:model UnionMember -m
php artisan make:model Role -m
php artisan make:model Permission -m
```

## Tạo Filament Resources

Tạo các resource để quản lý các model:

```bash
php artisan make:filament-resource User --generate
php artisan make:filament-resource Student --generate
php artisan make:filament-resource Union --generate
php artisan make:filament-resource UnionManager --generate
php artisan make:filament-resource Role --generate
php artisan make:filament-resource Permission --generate
```

## Cấu hình Model

### Model User

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'phone',
        'avatar',
        'role',
        'status',
        'last_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login' => 'datetime',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' || $this->role === 'union_manager';
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function unionManager()
    {
        return $this->hasOne(UnionManager::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }
}
```

### Model Student

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'date_of_birth',
        'gender',
        'faculty',
        'class',
        'course',
        'activity_points',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unionMemberships()
    {
        return $this->hasMany(UnionMember::class);
    }
}
```

### Model Union

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Union extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'status',
    ];

    public function managers()
    {
        return $this->hasMany(UnionManager::class);
    }

    public function members()
    {
        return $this->hasMany(UnionMember::class);
    }
}
```

## Cấu hình Filament Resource

### UserResource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Quản lý người dùng';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\FileUpload::make('avatar')
                    ->image()
                    ->directory('avatars'),
                Forms\Components\Select::make('role')
                    ->required()
                    ->options([
                        'admin' => 'Admin',
                        'union_manager' => 'Quản lý Đoàn hội',
                        'student' => 'Sinh viên',
                    ]),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'active' => 'Hoạt động',
                        'inactive' => 'Không hoạt động',
                        'banned' => 'Bị cấm',
                    ])
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'union_manager' => 'warning',
                        'student' => 'success',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'banned' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('last_login')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'union_manager' => 'Quản lý Đoàn hội',
                        'student' => 'Sinh viên',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Hoạt động',
                        'inactive' => 'Không hoạt động',
                        'banned' => 'Bị cấm',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

## Cấu hình Phân quyền với Filament Shield

Để quản lý quyền hạn trong Filament, bạn có thể sử dụng plugin Filament Shield:

```bash
composer require bezhansalleh/filament-shield
php artisan shield:install
```

Cấu hình Shield để sử dụng các model Role và Permission đã có:

```php
// config/filament-shield.php
'permission_model' => App\Models\Permission::class,
'role_model' => App\Models\Role::class,
```

## Lưu ý khi tích hợp

1. **ID tự tăng**: Filament hoạt động tốt nhất với ID tự tăng kiểu số nguyên, đã được cấu hình trong schema database.

2. **Quan hệ Eloquent**: Đảm bảo định nghĩa đầy đủ các quan hệ trong model để Filament có thể hiển thị và quản lý chúng.

3. **Form và Table**: Tùy chỉnh form và table trong Resource để hiển thị các trường phù hợp với thiết kế database.

4. **Validation**: Thêm các quy tắc validation trong form để đảm bảo dữ liệu nhập vào hợp lệ.

5. **Authorization**: Sử dụng Filament Shield để quản lý quyền hạn và phân quyền trong admin panel.

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleEnum;
use App\Enums\PermissionEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    
    // Sử dụng enum thay cho hằng số
    // const ROLE_ADMIN = 'admin';
    // const ROLE_UNION_MANAGER = 'union_manager';
    // const ROLE_STUDENT = 'student';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'full_name',
        'phone',
        'avatar',
        'role',
        'status',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
        ];
    }
    
    /**
     * Các quan hệ với các model khác
     */
    
    public function student()
    {
        return $this->hasOne(Student::class);
    }
    
    public function unionManager()
    {
        return $this->hasMany(UnionManager::class);
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
    
    /**
     * Các phương thức tiện ích
     */
    
    public function isAdmin()
    {
        return $this->role === RoleEnum::ADMIN->value;
    }
    
    public function isUnionManager()
    {
        return $this->role === RoleEnum::UNION_MANAGER->value;
    }
    
    public function isStudent()
    {
        return $this->role === RoleEnum::STUDENT->value;
    }
    
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }
        
        return $this->roles->contains($role);
    }
    
    public function hasPermission($permission)
    {
        return $this->roles->reduce(function ($carry, $role) use ($permission) {
            return $carry || $role->hasPermission($permission);
        }, false);
    }
    
    /**
     * Xác định xem người dùng có thể truy cập vào Filament Panel không
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isUnionManager();
    }
    
    /**
     * Lấy URL của avatar
     */
    public function getAvatarUrl(): string
    {
        if (!$this->avatar) {
            // Trả về URL avatar mặc định nếu không có avatar
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
        }
        
        // Nếu avatar là URL đầy đủ, trả về nguyên URL
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }
        
        // Nếu avatar là đường dẫn tương đối trong storage, trả về URL đầy đủ
        return Storage::disk('public')->url($this->avatar);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function eventComments()
    {
        return $this->hasMany(EventComment::class);
    }
}

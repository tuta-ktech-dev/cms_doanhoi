<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_system',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }
        
        return $this->permissions->contains($permission);
    }
    
    /**
     * Kiểm tra xem role có phải là role hệ thống không
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }
    
    /**
     * Kiểm tra xem role có phải là role enum không
     */
    public function isEnum(): bool
    {
        foreach (RoleEnum::cases() as $role) {
            if ($role->value === $this->name) {
                return true;
            }
        }
        
        return false;
    }
}

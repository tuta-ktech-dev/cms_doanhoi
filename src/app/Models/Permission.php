<?php

namespace App\Models;

use App\Enums\PermissionEnum;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_system',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
    
    /**
     * Kiểm tra xem permission có phải là permission hệ thống không
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }
    
    /**
     * Kiểm tra xem permission có phải là permission enum không
     */
    public function isEnum(): bool
    {
        foreach (PermissionEnum::cases() as $permission) {
            if ($permission->value === $this->name) {
                return true;
            }
        }
        
        return false;
    }
}

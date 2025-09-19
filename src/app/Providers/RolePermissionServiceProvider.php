<?php

namespace App\Providers;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\ServiceProvider;

class RolePermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Đăng ký các hooks để ngăn chặn thay đổi Roles và Permissions
        
        // Ngăn chặn tạo mới Role và Permission
        Role::creating(function ($role) {
            // Chỉ cho phép tạo role nếu nó nằm trong enum
            if (!$this->isValidRole($role->name)) {
                return false;
            }
        });
        
        Permission::creating(function ($permission) {
            // Chỉ cho phép tạo permission nếu nó nằm trong enum
            if (!$this->isValidPermission($permission->name)) {
                return false;
            }
        });
        
        // Ngăn chặn cập nhật Role và Permission
        Role::updating(function ($role) {
            // Không cho phép thay đổi tên role
            $original = $role->getOriginal('name');
            if ($original !== $role->name) {
                return false;
            }
        });
        
        Permission::updating(function ($permission) {
            // Không cho phép thay đổi tên permission
            $original = $permission->getOriginal('name');
            if ($original !== $permission->name) {
                return false;
            }
        });
        
        // Ngăn chặn xóa Role và Permission
        Role::deleting(function () {
            return false;
        });
        
        Permission::deleting(function () {
            return false;
        });
    }
    
    /**
     * Kiểm tra xem role có hợp lệ không
     */
    private function isValidRole(string $roleName): bool
    {
        foreach (RoleEnum::cases() as $role) {
            if ($role->value === $roleName) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Kiểm tra xem permission có hợp lệ không
     */
    private function isValidPermission(string $permissionName): bool
    {
        foreach (PermissionEnum::cases() as $permission) {
            if ($permission->value === $permissionName) {
                return true;
            }
        }
        
        return false;
    }
}

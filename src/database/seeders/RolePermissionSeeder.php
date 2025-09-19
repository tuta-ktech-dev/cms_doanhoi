<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các quyền hạn từ enum
        foreach (PermissionEnum::cases() as $permissionEnum) {
            Permission::create([
                'name' => $permissionEnum->value,
                'description' => $permissionEnum->description(),
            ]);
        }
        
        // Tạo các vai trò từ enum
        $adminRole = Role::create([
            'name' => RoleEnum::ADMIN->value,
            'description' => RoleEnum::ADMIN->description(),
        ]);
        
        $unionManagerRole = Role::create([
            'name' => RoleEnum::UNION_MANAGER->value,
            'description' => RoleEnum::UNION_MANAGER->description(),
        ]);
        
        $studentRole = Role::create([
            'name' => RoleEnum::STUDENT->value,
            'description' => RoleEnum::STUDENT->description(),
        ]);
        
        // Gán tất cả quyền cho Admin
        $adminRole->permissions()->attach(Permission::all());
        
        // Gán quyền cho Union Manager từ enum
        $unionManagerPermissionValues = array_map(
            fn($perm) => $perm->value, 
            PermissionEnum::getUnionManagerPermissions()
        );
        
        $unionManagerPermissions = Permission::whereIn('name', $unionManagerPermissionValues)->get();
        $unionManagerRole->permissions()->attach($unionManagerPermissions);
        
        // Gán quyền cho Student từ enum
        $studentPermissionValues = array_map(
            fn($perm) => $perm->value, 
            PermissionEnum::getStudentPermissions()
        );
        
        $studentPermissions = Permission::whereIn('name', $studentPermissionValues)->get();
        
        $studentRole->permissions()->attach($studentPermissions);
        
        // Gán vai trò cho admin đã tạo
        $admins = User::where('role', RoleEnum::ADMIN->value)->get();
        foreach ($admins as $admin) {
            $admin->roles()->attach($adminRole);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AddAttendancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permissions mới cho attendance nếu chưa có
        $attendancePermissions = [
            PermissionEnum::VIEW_ATTENDANCE,
            PermissionEnum::CREATE_ATTENDANCE,
            PermissionEnum::EDIT_ATTENDANCE,
            PermissionEnum::DELETE_ATTENDANCE,
        ];

        foreach ($attendancePermissions as $permissionEnum) {
            Permission::firstOrCreate(
                ['name' => $permissionEnum->value],
                [
                    'description' => $permissionEnum->description(),
                    'is_system' => true,
                ]
            );
        }

        // Gán permissions cho Admin (tất cả permissions)
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching(Permission::all());
        }

        // Gán permissions cho Union Manager
        $unionManagerRole = Role::where('name', 'union_manager')->first();
        if ($unionManagerRole) {
            $unionManagerPermissionValues = array_map(
                fn($perm) => $perm->value, 
                PermissionEnum::getUnionManagerPermissions()
            );
            
            $unionManagerPermissions = Permission::whereIn('name', $unionManagerPermissionValues)->get();
            $unionManagerRole->permissions()->syncWithoutDetaching($unionManagerPermissions);
        }

        $this->command->info('Attendance permissions added and assigned successfully!');
    }
}
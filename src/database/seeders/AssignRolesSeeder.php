<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gán roles cho tất cả users dựa trên role field
        $adminRole = Role::where('name', RoleEnum::ADMIN->value)->first();
        $unionManagerRole = Role::where('name', RoleEnum::UNION_MANAGER->value)->first();
        $studentRole = Role::where('name', RoleEnum::STUDENT->value)->first();

        // Gán role cho Admin users
        if ($adminRole) {
            $adminUsers = User::where('role', RoleEnum::ADMIN->value)->get();
            foreach ($adminUsers as $user) {
                if (!$user->roles->contains($adminRole)) {
                    $user->roles()->attach($adminRole);
                }
            }
        }

        // Gán role cho Union Manager users
        if ($unionManagerRole) {
            $unionManagerUsers = User::where('role', RoleEnum::UNION_MANAGER->value)->get();
            foreach ($unionManagerUsers as $user) {
                if (!$user->roles->contains($unionManagerRole)) {
                    $user->roles()->attach($unionManagerRole);
                }
            }
        }

        // Gán role cho Student users
        if ($studentRole) {
            $studentUsers = User::where('role', RoleEnum::STUDENT->value)->get();
            foreach ($studentUsers as $user) {
                if (!$user->roles->contains($studentRole)) {
                    $user->roles()->attach($studentRole);
                }
            }
        }

        $this->command->info('Đã gán roles cho tất cả users!');
    }
}
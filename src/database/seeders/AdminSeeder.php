<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo tài khoản admin mặc định
        User::create([
            'name' => 'admin',
            'email' => 'admin@cms.edu.vn',
            'password' => Hash::make('password'),
            'full_name' => 'Nguyễn Văn Admin',
            'phone' => '0901234567',
            'role' => RoleEnum::ADMIN->value,
            'status' => 'active',
        ]);

        // Tạo tài khoản admin thứ hai
        User::create([
            'name' => 'superadmin',
            'email' => 'superadmin@cms.edu.vn',
            'password' => Hash::make('password'),
            'full_name' => 'Trần Thị Super Admin',
            'phone' => '0901234568',
            'role' => RoleEnum::ADMIN->value,
            'status' => 'active',
        ]);
    }
}

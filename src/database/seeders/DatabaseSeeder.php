<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chạy các seeder theo thứ tự
        $this->call([
            // 1. Tạo tài khoản admin trước
            AdminSeeder::class,
            
            // 2. Tạo vai trò và phân quyền
            RolePermissionSeeder::class,
            
            // 3. Tạo đoàn hội và người quản lý
            UnionSeeder::class,
            
            // 4. Tạo sinh viên
            StudentSeeder::class,
            
            // 5. Tạo sự kiện
            EventSeeder::class,
            
            // 6. Tạo đăng ký sự kiện
            EventRegistrationSeeder::class,
            
            // 7. Tạo bình luận sự kiện
            EventCommentSeeder::class,
        ]);
    }
}

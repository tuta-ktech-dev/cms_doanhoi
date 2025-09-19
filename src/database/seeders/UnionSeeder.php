<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\Union;
use App\Models\UnionManager;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UnionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các đoàn hội mẫu
        $unions = [
            [
                'name' => 'Đoàn trường Đại học Công nghệ',
                'description' => 'Đoàn trường Đại học Công nghệ là tổ chức chính trị - xã hội của sinh viên trường Đại học Công nghệ.',
                'status' => 'active',
            ],
            [
                'name' => 'Hội Sinh viên Khoa CNTT',
                'description' => 'Hội Sinh viên Khoa Công nghệ thông tin trường Đại học Công nghệ.',
                'status' => 'active',
            ],
            [
                'name' => 'CLB Lập trình viên',
                'description' => 'Câu lạc bộ Lập trình viên trường Đại học Công nghệ.',
                'status' => 'active',
            ],
            [
                'name' => 'Đội tình nguyện xanh',
                'description' => 'Đội tình nguyện xanh trường Đại học Công nghệ.',
                'status' => 'active',
            ],
            [
                'name' => 'CLB Tiếng Anh',
                'description' => 'Câu lạc bộ Tiếng Anh trường Đại học Công nghệ.',
                'status' => 'active',
            ],
            [
                'name' => 'Hội Sinh viên Khoa Kinh tế',
                'description' => 'Hội Sinh viên Khoa Kinh tế trường Đại học Công nghệ.',
                'status' => 'active',
            ],
        ];

        foreach ($unions as $unionData) {
            Union::create($unionData);
        }

        // Tạo người quản lý cho mỗi đoàn hội
        $unions = Union::all();
        $managerNames = [
            'Nguyễn Văn Quản lý', 'Trần Thị Chủ nhiệm', 'Lê Văn Thư ký', 
            'Phạm Thị Phó chủ nhiệm', 'Hoàng Văn Trưởng ban', 'Vũ Thị Phụ trách'
        ];
        
        // Lấy vai trò Union Manager
        $unionManagerRole = Role::where('name', RoleEnum::UNION_MANAGER->value)->first();
        
        foreach ($unions as $index => $union) {
            $managerName = $managerNames[$index] ?? 'Nguyễn Văn Quản lý';
            
            // Tạo tài khoản người dùng
            $user = User::create([
                'name' => 'manager' . ($index + 1),
                'email' => 'manager' . ($index + 1) . '@cms.edu.vn',
                'password' => Hash::make('password'),
                'full_name' => $managerName,
                'phone' => '0' . rand(3, 9) . rand(10000000, 99999999),
                'role' => RoleEnum::UNION_MANAGER->value,
                'status' => 'active',
            ]);

            // Tạo liên kết với đoàn hội (chỉ 1 union cho mỗi manager)
            UnionManager::create([
                'user_id' => $user->id,
                'union_id' => $union->id,
                'position' => 'Chủ nhiệm',
            ]);
            
            // Gán vai trò
            if ($unionManagerRole) {
                $user->roles()->attach($unionManagerRole);
            }
        }
    }
}

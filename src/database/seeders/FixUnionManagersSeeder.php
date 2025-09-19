<?php

namespace Database\Seeders;

use App\Models\UnionManager;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixUnionManagersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa tất cả union managers hiện có
        UnionManager::truncate();
        
        // Lấy tất cả union manager users
        $unionManagerUsers = User::where('role', 'union_manager')->get();
        
        // Lấy tất cả unions
        $unions = \App\Models\Union::all();
        
        // Gán mỗi manager cho 1 union duy nhất
        foreach ($unionManagerUsers as $index => $user) {
            if ($index < $unions->count()) {
                UnionManager::create([
                    'user_id' => $user->id,
                    'union_id' => $unions[$index]->id,
                    'position' => 'Chủ nhiệm',
                ]);
            }
        }
        
        $this->command->info('Đã sửa lại union managers - mỗi manager chỉ quản lý 1 union!');
    }
}
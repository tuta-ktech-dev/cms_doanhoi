<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đọc dữ liệu từ file JSON
        $jsonPath = database_path('data/student_names.json');
        $data = json_decode(File::get($jsonPath), true);

        // Lấy vai trò sinh viên
        $studentRole = Role::where('name', RoleEnum::STUDENT->value)->first();

        // Tạo 100 sinh viên
        for ($i = 1; $i <= 100; $i++) {
            $firstName = $data['first_names'][array_rand($data['first_names'])];
            $lastName = $data['last_names'][array_rand($data['last_names'])];
            $faculty = $data['faculties'][array_rand($data['faculties'])];
            $class = $data['classes'][array_rand($data['classes'])];
            $course = $data['courses'][array_rand($data['courses'])];
            $gender = ['male', 'female'][array_rand(['male', 'female'])];

            // Tạo tên đầy đủ
            $fullName = $lastName . ' ' . $firstName;
            
            // Tạo email
            $email = strtolower($firstName . $lastName . $i . '@student.edu.vn');
            
            // Tạo số điện thoại
            $phone = '0' . rand(3, 9) . rand(10000000, 99999999);
            
            // Tạo mã sinh viên
            $studentId = 'SV' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            // Tạo ngày sinh (18-25 tuổi)
            $year = date('Y') - rand(18, 25);
            $month = rand(1, 12);
            $day = rand(1, 28);
            $dateOfBirth = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);

            // Tạo điểm rèn luyện (0-100)
            $activityPoints = rand(0, 100);

            // Tạo tài khoản người dùng
            $user = User::create([
                'name' => $firstName . $lastName . $i,
                'email' => $email,
                'password' => Hash::make('password'),
                'full_name' => $fullName,
                'phone' => $phone,
                'role' => RoleEnum::STUDENT->value,
                'status' => 'active',
            ]);
            
            // Tạo thông tin sinh viên
            $student = new Student([
                'student_id' => $studentId,
                'date_of_birth' => $dateOfBirth,
                'gender' => $gender,
                'faculty' => $faculty,
                'class' => $class,
                'course' => $course,
                'activity_points' => $activityPoints,
            ]);
            $user->student()->save($student);
            
            // Gán vai trò
            if ($studentRole) {
                $user->roles()->attach($studentRole);
            }
        }
    }
}

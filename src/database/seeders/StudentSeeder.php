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
     * Remove Vietnamese accents from string
     */
    private function removeVietnameseAccents($str): string
    {
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        
        foreach($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        
        return $str;
    }

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
            
            // Tạo email (loại bỏ dấu tiếng Việt)
            $emailName = $this->removeVietnameseAccents($firstName . $lastName . $i);
            $email = strtolower($emailName . '@student.edu.vn');
            
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

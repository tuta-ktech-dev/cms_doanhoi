<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Union;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unions = Union::all();
        
        if ($unions->isEmpty()) {
            $this->command->warn('Không có union nào để tạo sự kiện. Vui lòng chạy UnionSeeder trước.');
            return;
        }

        $events = [
            // Sự kiện Mùa hè xanh
            [
                'title' => 'Chiến dịch Mùa hè xanh 2024',
                'description' => 'Chiến dịch tình nguyện hè của Đoàn trường Đại học Công nghệ',
                'content' => '<h2>Giới thiệu</h2><p>Chiến dịch Mùa hè xanh là hoạt động tình nguyện truyền thống của Đoàn trường, mang đến cơ hội cho sinh viên tham gia các hoạt động xã hội ý nghĩa.</p><h3>Hoạt động chính:</h3><ul><li>Tình nguyện tại các vùng nông thôn</li><li>Dạy học cho trẻ em</li><li>Xây dựng cầu đường</li><li>Hoạt động bảo vệ môi trường</li><li>Chăm sóc người già neo đơn</li></ul><h3>Thời gian:</h3><p>Chiến dịch kéo dài 2 tuần với nhiều hoạt động đa dạng.</p>',
                'start_date' => now()->addDays(10)->setTime(7, 0),
                'end_date' => now()->addDays(24)->setTime(17, 0),
                'location' => 'Các tỉnh miền Tây Nam Bộ',
                'max_participants' => 200,
                'activity_points' => 15.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(5),
            ],
            
            // CLB Lập trình viên
            [
                'title' => 'Cuộc thi Lập trình ACM-ICPC 2024',
                'description' => 'Cuộc thi lập trình quốc tế dành cho sinh viên',
                'content' => '<h2>Thông tin cuộc thi</h2><p>ACM-ICPC là cuộc thi lập trình quốc tế uy tín nhất dành cho sinh viên đại học.</p><h3>Thể lệ:</h3><ul><li>Đội thi gồm 3 thành viên</li><li>Thời gian: 5 giờ</li><li>Ngôn ngữ: C++, Java, Python</li><li>Giải quyết các bài toán thuật toán</li></ul><h3>Giải thưởng:</h3><ul><li>Giải nhất: 10,000,000 VNĐ + Học bổng</li><li>Giải nhì: 7,000,000 VNĐ</li><li>Giải ba: 5,000,000 VNĐ</li></ul>',
                'start_date' => now()->addDays(15)->setTime(8, 0),
                'end_date' => now()->addDays(15)->setTime(13, 0),
                'location' => 'Phòng máy tính A - Tầng 2',
                'max_participants' => 60,
                'activity_points' => 12.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(8),
            ],
            
            // Hội Sinh viên Khoa CNTT
            [
                'title' => 'Hội thảo "Xu hướng AI và Machine Learning 2024"',
                'description' => 'Hội thảo chuyên sâu về trí tuệ nhân tạo và học máy',
                'content' => '<h2>Nội dung hội thảo</h2><p>Hội thảo mang đến cái nhìn tổng quan về xu hướng phát triển của AI và ML trong năm 2024.</p><h3>Diễn giả:</h3><ul><li>TS. Nguyễn Văn A - Chuyên gia AI tại Google</li><li>ThS. Trần Thị B - Data Scientist tại FPT</li><li>KS. Lê Văn C - ML Engineer tại Viettel</li></ul><h3>Chủ đề:</h3><ul><li>Large Language Models (LLM)</li><li>Computer Vision</li><li>Deep Learning</li><li>Ứng dụng AI trong thực tế</li></ul>',
                'start_date' => now()->addDays(20)->setTime(9, 0),
                'end_date' => now()->addDays(20)->setTime(16, 0),
                'location' => 'Hội trường lớn - Tầng 4',
                'max_participants' => 300,
                'activity_points' => 8.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(15),
            ],
            
            // CLB Tiếng Anh
            [
                'title' => 'Cuộc thi "English Speaking Contest 2024"',
                'description' => 'Cuộc thi hùng biện tiếng Anh dành cho sinh viên',
                'content' => '<h2>Thông tin cuộc thi</h2><p>Cuộc thi hùng biện tiếng Anh thường niên của CLB Tiếng Anh, tạo cơ hội cho sinh viên thể hiện khả năng nói tiếng Anh.</p><h3>Thể lệ:</h3><ul><li>Vòng 1: Thuyết trình 3 phút về chủ đề tự chọn</li><li>Vòng 2: Tranh luận theo cặp</li><li>Vòng 3: Thuyết trình 5 phút về chủ đề bốc thăm</li></ul><h3>Tiêu chí đánh giá:</h3><ul><li>Phát âm và ngữ điệu</li><li>Nội dung và logic</li><li>Phong thái thuyết trình</li><li>Khả năng phản biện</li></ul>',
                'start_date' => now()->addDays(25)->setTime(14, 0),
                'end_date' => now()->addDays(25)->setTime(17, 0),
                'location' => 'Phòng hội thảo B - Tầng 3',
                'max_participants' => 50,
                'activity_points' => 6.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(20),
            ],
            
            // Hội Sinh viên Khoa Kinh tế
            [
                'title' => 'Workshop "Kỹ năng Thuyết trình và Giao tiếp"',
                'description' => 'Workshop phát triển kỹ năng thuyết trình và giao tiếp cho sinh viên',
                'content' => '<h2>Mục tiêu</h2><p>Workshop giúp sinh viên nâng cao kỹ năng thuyết trình và giao tiếp - những kỹ năng quan trọng trong công việc tương lai.</p><h3>Nội dung:</h3><ul><li>Kỹ thuật thuyết trình hiệu quả</li><li>Ngôn ngữ cơ thể và giọng nói</li><li>Xây dựng slide thuyết trình</li><li>Xử lý câu hỏi và phản biện</li><li>Giao tiếp trong môi trường doanh nghiệp</li></ul><h3>Hoạt động thực hành:</h3><ul><li>Thuyết trình nhóm</li><li>Phản hồi và đánh giá</li><li>Case study thực tế</li></ul>',
                'start_date' => now()->addDays(30)->setTime(9, 0),
                'end_date' => now()->addDays(30)->setTime(16, 0),
                'location' => 'Phòng hội thảo A - Tầng 2',
                'max_participants' => 40,
                'activity_points' => 5.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(25),
            ],
            
            // Đội tình nguyện xanh
            [
                'title' => 'Chiến dịch "Ngày Trái Đất 2024"',
                'description' => 'Hoạt động tình nguyện bảo vệ môi trường nhân Ngày Trái Đất',
                'content' => '<h2>Mục đích</h2><p>Chiến dịch nhằm nâng cao ý thức bảo vệ môi trường trong cộng đồng sinh viên và người dân.</p><h3>Hoạt động:</h3><ul><li>Thu gom rác thải tại công viên</li><li>Trồng cây xanh</li><li>Tuyên truyền về tái chế</li><li>Triển lãm sản phẩm thân thiện môi trường</li><li>Workshop làm đồ tái chế</li></ul><h3>Địa điểm:</h3><p>Công viên Lê Văn Tám và khuôn viên trường</p><h3>Vật dụng cần mang:</h3><ul><li>Găng tay</li><li>Nón</li><li>Nước uống</li><li>Tinh thần nhiệt huyết</li></ul>',
                'start_date' => now()->addDays(35)->setTime(7, 0),
                'end_date' => now()->addDays(35)->setTime(12, 0),
                'location' => 'Công viên Lê Văn Tám & Khuôn viên trường',
                'max_participants' => 100,
                'activity_points' => 4.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(30),
            ],
            
            // CLB Lập trình viên - Workshop
            [
                'title' => 'Workshop "Git và GitHub cho Developer"',
                'description' => 'Workshop hướng dẫn sử dụng Git và GitHub cho sinh viên lập trình',
                'content' => '<h2>Nội dung workshop</h2><p>Workshop cung cấp kiến thức cơ bản và nâng cao về Git và GitHub - công cụ không thể thiếu của developer.</p><h3>Chủ đề:</h3><ul><li>Giới thiệu về Git và GitHub</li><li>Các lệnh Git cơ bản</li><li>Branch và Merge</li><li>Pull Request và Code Review</li><li>GitHub Actions</li><li>Best practices</li></ul><h3>Yêu cầu:</h3><ul><li>Mang laptop</li><li>Cài đặt Git trước</li><li>Tạo tài khoản GitHub</li></ul><h3>Thực hành:</h3><p>Workshop có phần thực hành trực tiếp với các bài tập thực tế.</p>',
                'start_date' => now()->addDays(40)->setTime(14, 0),
                'end_date' => now()->addDays(40)->setTime(17, 0),
                'location' => 'Phòng máy tính C - Tầng 1',
                'max_participants' => 30,
                'activity_points' => 3.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(35),
            ],
            
            // Hội Sinh viên Khoa CNTT - Networking
            [
                'title' => 'Networking Event "Meet the Alumni"',
                'description' => 'Sự kiện gặp gỡ và chia sẻ kinh nghiệm với cựu sinh viên',
                'content' => '<h2>Mục đích</h2><p>Sự kiện tạo cơ hội cho sinh viên gặp gỡ và học hỏi kinh nghiệm từ các cựu sinh viên đang làm việc trong ngành CNTT.</p><h3>Khách mời:</h3><ul><li>Anh Nguyễn Văn D - Senior Developer tại Shopee</li><li>Chị Trần Thị E - Product Manager tại Grab</li><li>Anh Lê Văn F - Tech Lead tại VNG</li><li>Chị Phạm Thị G - Data Scientist tại VinGroup</li></ul><h3>Nội dung:</h3><ul><li>Chia sẻ kinh nghiệm làm việc</li><li>Con đường phát triển sự nghiệp</li><li>Kỹ năng cần thiết trong ngành</li><li>Networking và tìm việc</li></ul>',
                'start_date' => now()->addDays(45)->setTime(18, 0),
                'end_date' => now()->addDays(45)->setTime(21, 0),
                'location' => 'Hội trường A - Tầng 3',
                'max_participants' => 80,
                'activity_points' => 4.0,
                'status' => 'published',
                'is_registration_open' => true,
                'registration_deadline' => now()->addDays(40),
            ],
        ];

        foreach ($events as $eventData) {
            $union = $unions->random();
            $eventData['union_id'] = $union->id;
            Event::create($eventData);
        }
    }
}

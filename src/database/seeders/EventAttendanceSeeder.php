<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả sự kiện đã có đăng ký
        $events = Event::whereHas('registrations', function ($query) {
            $query->where('status', 'approved');
        })->get();

        foreach ($events as $event) {
            // Lấy danh sách người đã đăng ký và được duyệt
            $approvedRegistrations = $event->registrations()
                ->where('status', 'approved')
                ->with('user')
                ->get();

            // Tạo điểm danh cho một số người đã đăng ký
            $attendanceCount = min(rand(5, min(15, $approvedRegistrations->count())), $approvedRegistrations->count());
            $selectedRegistrations = $approvedRegistrations->random($attendanceCount);

            foreach ($selectedRegistrations as $registration) {
                // Tạo thời gian điểm danh ngẫu nhiên trong khoảng thời gian sự kiện
                $eventStart = $event->start_date;
                $eventEnd = $event->end_date ?? $eventStart->addHours(2);
                
                $attendedAt = fake()->dateTimeBetween($eventStart, $eventEnd);
                
                // Tạo trạng thái điểm danh ngẫu nhiên
                $statuses = ['present', 'present', 'present', 'late', 'absent']; // Ưu tiên có mặt
                $status = fake()->randomElement($statuses);
                
                // Tạo ghi chú ngẫu nhiên
                $notes = null;
                if ($status === 'late') {
                    $notes = fake()->randomElement([
                        'Đến muộn 15 phút',
                        'Đến muộn 30 phút do tắc đường',
                        'Đến muộn 10 phút',
                        'Đến muộn do xe hỏng',
                    ]);
                } elseif ($status === 'absent') {
                    $notes = fake()->randomElement([
                        'Không tham gia',
                        'Báo vắng có lý do',
                        'Có việc đột xuất',
                        'Bị ốm',
                    ]);
                } elseif ($status === 'present') {
                    $notes = fake()->randomElement([
                        'Tham gia đầy đủ',
                        'Tham gia tích cực',
                        'Có mặt đúng giờ',
                        null,
                        null,
                        null, // Nhiều trường hợp không có ghi chú
                    ]);
                }

                EventAttendance::create([
                    'event_id' => $event->id,
                    'user_id' => $registration->user_id,
                    'registered_by' => fake()->randomElement([1, 2, 3]), // Admin hoặc Union Manager
                    'attended_at' => $attendedAt,
                    'status' => $status,
                    'notes' => $notes,
                ]);
            }
        }

        $this->command->info('Event attendance data seeded successfully!');
    }
}
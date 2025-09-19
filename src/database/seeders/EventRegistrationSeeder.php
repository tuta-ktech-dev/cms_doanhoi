<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Tạo đăng ký sự kiện cho sinh viên...');

        // Lấy tất cả sinh viên
        $students = User::where('role', RoleEnum::STUDENT->value)->get();
        
        if ($students->isEmpty()) {
            $this->command->warn('Không có sinh viên nào để tạo đăng ký. Vui lòng chạy StudentSeeder trước.');
            return;
        }

        // Lấy tất cả sự kiện đã xuất bản
        $events = Event::where('status', 'published')
            ->where('is_registration_open', true)
            ->get();
        
        if ($events->isEmpty()) {
            $this->command->warn('Không có sự kiện nào để đăng ký. Vui lòng chạy EventSeeder trước.');
            return;
        }

        $registrationCount = 0;
        $statuses = ['pending', 'approved', 'rejected'];
        $statusWeights = [0.6, 0.35, 0.05]; // 60% pending, 35% approved, 5% rejected

        foreach ($events as $event) {
            // Số lượng đăng ký cho mỗi sự kiện (30-80% max_participants)
            $maxRegistrations = $event->max_participants 
                ? rand(ceil($event->max_participants * 0.3), ceil($event->max_participants * 0.8))
                : rand(20, 50);

            // Chọn ngẫu nhiên sinh viên để đăng ký
            $selectedStudents = $students->random(min($maxRegistrations, $students->count()));

            foreach ($selectedStudents as $student) {
                // Kiểm tra xem sinh viên đã đăng ký sự kiện này chưa
                $existingRegistration = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $student->id)
                    ->first();

                if ($existingRegistration) {
                    continue; // Bỏ qua nếu đã đăng ký
                }

                // Chọn trạng thái dựa trên trọng số
                $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
                
                // Tạo đăng ký
                $registration = EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => $student->id,
                    'status' => $status,
                    'notes' => $this->generateRegistrationNotes($status),
                    'registered_at' => now()->subDays(rand(1, 10)), // Đăng ký trong 10 ngày qua
                ]);

                // Nếu đã được duyệt, thêm thông tin duyệt
                if ($status === 'approved') {
                    $registration->update([
                        'approved_at' => $registration->registered_at->addDays(rand(1, 3)),
                        'approved_by' => $this->getRandomApprover(),
                    ]);
                }

                $registrationCount++;
            }
        }

        $this->command->info("Đã tạo {$registrationCount} đăng ký sự kiện!");
    }

    /**
     * Chọn trạng thái dựa trên trọng số
     */
    private function getWeightedRandomStatus(array $statuses, array $weights): string
    {
        $random = mt_rand() / mt_getrandmax();
        $cumulative = 0;

        for ($i = 0; $i < count($statuses); $i++) {
            $cumulative += $weights[$i];
            if ($random <= $cumulative) {
                return $statuses[$i];
            }
        }

        return $statuses[0]; // Fallback
    }

    /**
     * Tạo ghi chú đăng ký
     */
    private function generateRegistrationNotes(string $status): ?string
    {
        $notes = [
            'pending' => [
                'Rất mong được tham gia sự kiện này!',
                'Hy vọng có cơ hội học hỏi thêm kiến thức.',
                'Quan tâm đến chủ đề của sự kiện.',
                'Muốn mở rộng mạng lưới bạn bè.',
                'Tìm kiếm cơ hội phát triển kỹ năng.',
            ],
            'approved' => [
                'Đã được duyệt tham gia.',
                'Sinh viên có thành tích học tập tốt.',
                'Có kinh nghiệm liên quan đến sự kiện.',
                'Thể hiện sự quan tâm tích cực.',
                'Phù hợp với tiêu chí của sự kiện.',
            ],
            'rejected' => [
                'Không đủ điều kiện tham gia.',
                'Đã đăng ký quá nhiều sự kiện khác.',
                'Thiếu kinh nghiệm cần thiết.',
                'Không phù hợp với mục tiêu sự kiện.',
                'Cần ưu tiên cho sinh viên khác.',
            ],
        ];

        $statusNotes = $notes[$status] ?? [];
        return $statusNotes ? $statusNotes[array_rand($statusNotes)] : null;
    }

    /**
     * Lấy người duyệt ngẫu nhiên (Union Manager hoặc Admin)
     */
    private function getRandomApprover(): ?int
    {
        $approvers = User::whereIn('role', [
            RoleEnum::ADMIN->value,
            RoleEnum::UNION_MANAGER->value
        ])->pluck('id')->toArray();

        return $approvers ? $approvers[array_rand($approvers)] : null;
    }
}

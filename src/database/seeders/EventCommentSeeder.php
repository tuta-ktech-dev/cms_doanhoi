<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventRegistration;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Tạo bình luận sự kiện...');

        // Lấy tất cả sinh viên
        $students = User::where('role', RoleEnum::STUDENT->value)->get();
        
        if ($students->isEmpty()) {
            $this->command->warn('Không có sinh viên nào để tạo bình luận. Vui lòng chạy StudentSeeder trước.');
            return;
        }

        // Lấy tất cả sự kiện đã xuất bản
        $events = Event::where('status', 'published')->get();
        
        if ($events->isEmpty()) {
            $this->command->warn('Không có sự kiện nào để bình luận. Vui lòng chạy EventSeeder trước.');
            return;
        }

        $commentCount = 0;

        foreach ($events as $event) {
            // Số lượng bình luận cho mỗi sự kiện (5-15 bình luận)
            $commentCountForEvent = rand(5, 15);
            
            // Chọn ngẫu nhiên sinh viên để bình luận
            $commenters = $students->random(min($commentCountForEvent, $students->count()));

            foreach ($commenters as $student) {
                // Tạo bình luận chính
                $comment = EventComment::create([
                    'event_id' => $event->id,
                    'user_id' => $student->id,
                    'content' => $this->generateCommentContent($event),
                    'is_approved' => true, // Tất cả bình luận đều được duyệt
                ]);

                $commentCount++;

                // 30% khả năng có bình luận phản hồi
                if (rand(1, 100) <= 30) {
                    $replyStudent = $students->where('id', '!=', $student->id)->random();
                    
                    EventComment::create([
                        'event_id' => $event->id,
                        'user_id' => $replyStudent->id,
                        'parent_id' => $comment->id,
                        'content' => $this->generateReplyContent(),
                        'is_approved' => true,
                    ]);

                    $commentCount++;
                }
            }
        }

        $this->command->info("Đã tạo {$commentCount} bình luận sự kiện!");
    }

    /**
     * Tạo nội dung bình luận dựa trên loại sự kiện
     */
    private function generateCommentContent(Event $event): string
    {
        $comments = [
            'general' => [
                'Sự kiện này rất thú vị! Tôi rất mong được tham gia.',
                'Cảm ơn ban tổ chức đã tổ chức sự kiện ý nghĩa này.',
                'Thời gian và địa điểm rất phù hợp với lịch học của tôi.',
                'Nội dung sự kiện rất hấp dẫn và bổ ích.',
                'Tôi đã đăng ký và rất háo hức chờ đợi sự kiện.',
                'Sự kiện này sẽ giúp tôi phát triển nhiều kỹ năng.',
                'Điểm rèn luyện khá cao, rất đáng để tham gia.',
                'Ban tổ chức chuẩn bị rất chu đáo.',
                'Tôi sẽ giới thiệu sự kiện này cho bạn bè.',
                'Hy vọng sự kiện sẽ thành công tốt đẹp.',
            ],
            'programming' => [
                'Cuộc thi lập trình này sẽ giúp tôi cải thiện kỹ năng coding.',
                'Tôi đã chuẩn bị kỹ lưỡng cho cuộc thi này.',
                'Thể lệ cuộc thi rất công bằng và minh bạch.',
                'Giải thưởng hấp dẫn, tôi sẽ cố gắng hết sức.',
                'Đây là cơ hội tốt để test khả năng lập trình.',
                'Tôi mong được gặp gỡ các bạn cùng đam mê lập trình.',
                'Cuộc thi sẽ giúp tôi học hỏi thêm nhiều thuật toán.',
                'Ban giám khảo đều là những chuyên gia uy tín.',
                'Tôi sẽ tham gia với tinh thần học hỏi.',
                'Hy vọng cuộc thi sẽ diễn ra suôn sẻ.',
            ],
            'volunteer' => [
                'Hoạt động tình nguyện này rất ý nghĩa.',
                'Tôi muốn đóng góp một phần nhỏ cho cộng đồng.',
                'Đây là cơ hội để trải nghiệm cuộc sống thực tế.',
                'Tôi sẽ học được nhiều điều từ hoạt động này.',
                'Tham gia tình nguyện giúp tôi trưởng thành hơn.',
                'Tôi rất vui khi được giúp đỡ mọi người.',
                'Hoạt động này sẽ để lại nhiều kỷ niệm đẹp.',
                'Tôi mong được gặp gỡ những người bạn mới.',
                'Đây là cách tốt để sử dụng thời gian rảnh.',
                'Tôi tin rằng mình sẽ có những trải nghiệm quý báu.',
            ],
            'workshop' => [
                'Workshop này sẽ giúp tôi nâng cao kỹ năng.',
                'Nội dung workshop rất thiết thực và hữu ích.',
                'Tôi mong được học hỏi từ các chuyên gia.',
                'Đây là cơ hội tốt để phát triển bản thân.',
                'Tôi sẽ tích cực tham gia các hoạt động thực hành.',
                'Workshop có cấu trúc rất khoa học và logic.',
                'Tôi tin rằng sẽ học được nhiều kiến thức mới.',
                'Giảng viên đều là những người có kinh nghiệm.',
                'Tôi sẽ áp dụng những gì học được vào thực tế.',
                'Hy vọng workshop sẽ đạt được mục tiêu đề ra.',
            ],
        ];

        // Phân loại sự kiện dựa trên tiêu đề
        $eventType = 'general';
        $title = strtolower($event->title);
        
        if (str_contains($title, 'lập trình') || str_contains($title, 'programming') || str_contains($title, 'acm')) {
            $eventType = 'programming';
        } elseif (str_contains($title, 'tình nguyện') || str_contains($title, 'mùa hè xanh') || str_contains($title, 'trái đất')) {
            $eventType = 'volunteer';
        } elseif (str_contains($title, 'workshop') || str_contains($title, 'hội thảo') || str_contains($title, 'kỹ năng')) {
            $eventType = 'workshop';
        }

        $typeComments = $comments[$eventType] ?? $comments['general'];
        return $typeComments[array_rand($typeComments)];
    }

    /**
     * Tạo nội dung phản hồi
     */
    private function generateReplyContent(): string
    {
        $replies = [
            'Tôi cũng nghĩ vậy!',
            'Hoàn toàn đồng ý với bạn.',
            'Cảm ơn bạn đã chia sẻ.',
            'Tôi cũng rất mong được tham gia.',
            'Ý kiến của bạn rất hay.',
            'Tôi cũng có suy nghĩ tương tự.',
            'Chúng ta sẽ cùng nhau tham gia nhé.',
            'Tôi tin rằng sự kiện sẽ rất thành công.',
            'Cảm ơn bạn đã động viên.',
            'Chúng ta hãy cùng chuẩn bị thật tốt.',
        ];

        return $replies[array_rand($replies)];
    }
}

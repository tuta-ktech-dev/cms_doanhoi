<?php

namespace App\Observers;

use App\Enums\RoleEnum;
use App\Http\Controllers\Api\NotificationController;
use App\Models\Event;
use App\Models\User;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        // Nếu sự kiện được tạo với status = 'published', gửi thông báo cho tất cả sinh viên
        if ($event->status === 'published') {
            $this->notifyAllStudents($event);
        }
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        // Kiểm tra nếu status thay đổi từ khác sang 'published'
        if ($event->wasChanged('status') && $event->status === 'published') {
            $this->notifyAllStudents($event);
        }
    }

    /**
     * Gửi thông báo sự kiện mới cho tất cả sinh viên
     */
    private function notifyAllStudents(Event $event): void
    {
        // Lấy tất cả users có role là STUDENT
        $students = User::where('role', RoleEnum::STUDENT->value)->get();

        // Tạo thông báo cho từng sinh viên
        foreach ($students as $student) {
            NotificationController::createNewEventNotification($student, $event);
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/student/notifications",
     *     summary="Lấy danh sách thông báo",
     *     description="Lấy danh sách thông báo của sinh viên",
     *     tags={"Student Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Lọc theo loại thông báo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"registration_success", "unregistration_success", "attendance_success", "new_event"})
     *     ),
     *     @OA\Parameter(
     *         name="read",
     *         in="query",
     *         description="Lọc theo trạng thái đọc (true/false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số item per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách thông báo",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="notifications", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="pagination", type="object"),
     *                 @OA\Property(property="unread_count", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Lọc theo type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Lọc theo trạng thái đọc
        if ($request->has('read')) {
            if ($request->read === 'true' || $request->read === true) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        $perPage = $request->get('per_page', 15);
        $notifications = $query->paginate($perPage);

        // Đếm số thông báo chưa đọc
        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Transform data
        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'is_read' => $notification->isRead(),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'unread_count' => $unreadCount,
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/student/notifications/{id}/read",
     *     summary="Đánh dấu thông báo đã đọc",
     *     description="Đánh dấu một thông báo là đã đọc",
     *     tags={"Student Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của thông báo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đánh dấu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đã đánh dấu đọc")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Thông báo không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Thông báo không tồn tại")
     *         )
     *     )
     * )
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $notification = Notification::where('user_id', $user->id)
            ->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Thông báo không tồn tại'
            ], 404);
        }

        if ($notification->isRead()) {
            return response()->json([
                'success' => true,
                'message' => 'Thông báo đã được đánh dấu đọc trước đó'
            ]);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đọc'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/student/notifications/read-all",
     *     summary="Đánh dấu tất cả thông báo đã đọc",
     *     description="Đánh dấu tất cả thông báo của sinh viên là đã đọc",
     *     tags={"Student Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Đánh dấu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đã đánh dấu tất cả thông báo là đã đọc"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="marked_count", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $markedCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc',
            'data' => [
                'marked_count' => $markedCount
            ]
        ]);
    }

    /**
     * Tạo thông báo đăng ký thành công
     */
    public static function createRegistrationSuccessNotification($user, $event): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'registration_success',
            'title' => 'Đăng ký thành công',
            'message' => "Bạn đã đăng ký thành công sự kiện: {$event->title}",
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_start_date' => $event->start_date,
                'event_location' => $event->location,
            ],
        ]);
    }

    /**
     * Tạo thông báo hủy đăng ký thành công
     */
    public static function createUnregistrationSuccessNotification($user, $event): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'unregistration_success',
            'title' => 'Hủy đăng ký thành công',
            'message' => "Bạn đã hủy đăng ký sự kiện: {$event->title}",
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_start_date' => $event->start_date,
            ],
        ]);
    }

    /**
     * Tạo thông báo điểm danh thành công
     */
    public static function createAttendanceSuccessNotification($user, $event, $activityPoints = 0): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'attendance_success',
            'title' => 'Điểm danh thành công',
            'message' => "Bạn đã điểm danh thành công sự kiện: {$event->title}" . ($activityPoints > 0 ? ". Bạn nhận được {$activityPoints} điểm hoạt động." : ''),
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'activity_points' => $activityPoints,
            ],
        ]);
    }

    /**
     * Tạo thông báo sự kiện mới
     */
    public static function createNewEventNotification($user, $event): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'new_event',
            'title' => 'Sự kiện mới',
            'message' => "Có sự kiện mới: {$event->title}. Bắt đầu vào {$event->start_date->format('d/m/Y H:i')}",
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_description' => $event->description,
                'event_start_date' => $event->start_date,
                'event_end_date' => $event->end_date,
                'event_location' => $event->location,
                'event_image_url' => $event->getImageUrl(),
                'activity_points' => $event->activity_points,
                'union_id' => $event->union_id,
                'union_name' => $event->union->name ?? null,
            ],
        ]);
    }
}


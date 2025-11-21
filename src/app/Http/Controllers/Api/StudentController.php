<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\NotificationController;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventAttendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class StudentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/student/events",
     *     summary="Lấy danh sách sự kiện",
     *     description="Lấy danh sách tất cả sự kiện có thể đăng ký",
     *     tags={"Student Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lọc theo trạng thái sự kiện",
     *         required=false,
     *         @OA\Schema(type="string", enum={"upcoming", "ongoing", "completed"})
     *     ),
     *     @OA\Parameter(
     *         name="union_id",
     *         in="query",
     *         description="Lọc theo đoàn hội",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     *         description="Danh sách sự kiện",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="events", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="pagination", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function getEvents(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Event::with(['union', 'registrations' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
        ->where('status', 'published')
        ->orderBy('start_date', 'asc');

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->where('start_date', '>', now());
                    break;
                case 'ongoing':
                    $query->where('start_date', '<=', now())
                          ->where('end_date', '>=', now());
                    break;
                case 'completed':
                    $query->where('end_date', '<', now());
                    break;
            }
        }

        // Filter by union
        if ($request->has('union_id')) {
            $query->where('union_id', $request->union_id);
        }

        $perPage = $request->get('per_page', 15);
        $events = $query->paginate($perPage);

        // Transform data
        $events->getCollection()->transform(function ($event) use ($user) {
            $registration = $event->registrations->first();
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'location' => $event->location,
                'max_participants' => $event->max_participants,
                'current_participants' => $event->registrations()->where('status', 'approved')->count(),
                'activity_points' => $event->activity_points,
                'image_url' => $event->getImageUrl(),
                'union' => [
                    'id' => $event->union->id,
                    'name' => $event->union->name,
                    'logo_url' => $event->union->getLogoUrl(),
                ],
                'registration_status' => $registration ? $registration->status : null,
                'can_register' => $this->canRegister($event, $user),
                'status' => $this->getEventStatus($event),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events->items(),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/student/events/{id}",
     *     summary="Lấy chi tiết sự kiện",
     *     description="Lấy thông tin chi tiết của một sự kiện",
     *     tags={"Student Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của sự kiện",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chi tiết sự kiện",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sự kiện không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sự kiện không tồn tại")
     *         )
     *     )
     * )
     */
    public function getEvent(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $event = Event::with(['union', 'registrations' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
        ->where('status', 'published')
        ->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện không tồn tại'
            ], 404);
        }

        $registration = $event->registrations->first();
        $attendance = null;
        
        if ($registration && $registration->status === 'approved') {
            $attendance = EventAttendance::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();
        }

        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'max_participants' => $event->max_participants,
            'current_participants' => $event->registrations()->where('status', 'approved')->count(),
            'activity_points' => $event->activity_points,
            'image_url' => $event->getImageUrl(),
            'union' => [
                'id' => $event->union->id,
                'name' => $event->union->name,
                'description' => $event->union->description,
                'logo_url' => $event->union->getLogoUrl(),
            ],
            'registration' => $registration ? [
                'id' => $registration->id,
                'status' => $registration->status,
                'registered_at' => $registration->created_at,
                'notes' => $registration->notes,
            ] : null,
            'attendance' => $attendance ? [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'attended_at' => $attendance->attended_at,
                'notes' => $attendance->notes,
            ] : null,
            'can_register' => $this->canRegister($event, $user),
            'can_unregister' => $this->canUnregister($event, $user),
            'status' => $this->getEventStatus($event),
        ];

        return response()->json([
            'success' => true,
            'data' => $eventData
        ]);
    }

    /**
     * @OA\Post(
     *     path="/student/events/{id}/register",
     *     summary="Đăng ký tham gia sự kiện",
     *     description="Đăng ký tham gia một sự kiện",
     *     tags={"Student Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của sự kiện",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string", example="Ghi chú đăng ký")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đăng ký thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đăng ký thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Không thể đăng ký",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không thể đăng ký sự kiện này")
     *         )
     *     )
     * )
     */
    public function registerEvent(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $event = Event::where('status', 'published')->find($id);
        
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện không tồn tại'
            ], 404);
        }

        // Check if already registered
        $existingRegistration = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đăng ký sự kiện này rồi'
            ], 400);
        }

        // Check if can register
        if (!$this->canRegister($event, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đăng ký sự kiện này'
            ], 400);
        }

        // Check if event is full
        $currentParticipants = $event->registrations()->where('status', 'approved')->count();
        if ($event->max_participants && $currentParticipants >= $event->max_participants) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện đã đầy'
            ], 400);
        }

        // Create registration
        $registration = EventRegistration::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        // Tạo thông báo đăng ký thành công
        NotificationController::createRegistrationSuccessNotification($user, $event);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => [
                'registration_id' => $registration->id,
                'status' => $registration->status,
                'registered_at' => $registration->created_at,
            ]
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/student/events/{id}/unregister",
     *     summary="Hủy đăng ký sự kiện",
     *     description="Hủy đăng ký tham gia một sự kiện",
     *     tags={"Student Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của sự kiện",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hủy đăng ký thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Hủy đăng ký thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Không thể hủy đăng ký",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không thể hủy đăng ký")
     *         )
     *     )
     * )
     */
    public function unregisterEvent(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        $event = Event::find($id);
        
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện không tồn tại'
            ], 404);
        }

        $registration = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng ký sự kiện này'
            ], 400);
        }

        // Check if can unregister
        if (!$this->canUnregister($event, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đăng ký sự kiện này'
            ], 400);
        }

        // Lưu thông tin event trước khi xóa registration
        $event = $registration->event;

        // Delete registration
        $registration->delete();

        // Tạo thông báo hủy đăng ký thành công
        NotificationController::createUnregistrationSuccessNotification($user, $event);

        return response()->json([
            'success' => true,
            'message' => 'Hủy đăng ký thành công'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/student/registrations",
     *     summary="Lấy danh sách đăng ký",
     *     description="Lấy danh sách đăng ký sự kiện của sinh viên",
     *     tags={"Student Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lọc theo trạng thái đăng ký",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách đăng ký",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getRegistrations(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = EventRegistration::with(['event.union'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->get();

        $registrations->transform(function ($registration) {
            return [
                'id' => $registration->id,
                'status' => $registration->status,
                'status_label' => $this->getStatusLabel($registration->status),
                'registered_at' => $registration->created_at,
                'notes' => $registration->notes,
                'event' => [
                    'id' => $registration->event->id,
                    'title' => $registration->event->title,
                    'start_date' => $registration->event->start_date,
                    'end_date' => $registration->event->end_date,
                    'location' => $registration->event->location,
                    'activity_points' => $registration->event->activity_points,
                    'union' => [
                        'id' => $registration->event->union->id,
                        'name' => $registration->event->union->name,
                    ],
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $registrations
        ]);
    }

    /**
     * @OA\Get(
     *     path="/student/attendance",
     *     summary="Lấy danh sách điểm danh",
     *     description="Lấy danh sách điểm danh của sinh viên",
     *     tags={"Student Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lọc theo trạng thái điểm danh",
     *         required=false,
     *         @OA\Schema(type="string", enum={"present", "absent", "late", "excused"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách điểm danh",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = EventAttendance::with(['event.union'])
            ->where('user_id', $user->id)
            ->orderBy('attended_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendance = $query->get();

        $attendance->transform(function ($attendance) {
            return [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'status_label' => $this->getAttendanceStatusLabel($attendance->status),
                'attended_at' => $attendance->attended_at,
                'notes' => $attendance->notes,
                'activity_points_earned' => $attendance->status === 'present' ? $attendance->event->activity_points : 0,
                'event' => [
                    'id' => $attendance->event->id,
                    'title' => $attendance->event->title,
                    'start_date' => $attendance->event->start_date,
                    'end_date' => $attendance->event->end_date,
                    'activity_points' => $attendance->event->activity_points,
                    'union' => [
                        'id' => $attendance->event->union->id,
                        'name' => $attendance->event->union->name,
                    ],
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * @OA\Get(
     *     path="/student/statistics",
     *     summary="Lấy thống kê sinh viên",
     *     description="Lấy thống kê tổng quan của sinh viên",
     *     tags={"Student Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thống kê sinh viên",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Registration statistics
        $totalRegistrations = EventRegistration::where('user_id', $user->id)->count();
        $approvedRegistrations = EventRegistration::where('user_id', $user->id)->where('status', 'approved')->count();
        $pendingRegistrations = EventRegistration::where('user_id', $user->id)->where('status', 'pending')->count();
        $rejectedRegistrations = EventRegistration::where('user_id', $user->id)->where('status', 'rejected')->count();

        // Attendance statistics
        $totalAttendance = EventAttendance::where('user_id', $user->id)->count();
        $presentCount = EventAttendance::where('user_id', $user->id)->where('status', 'present')->count();
        $absentCount = EventAttendance::where('user_id', $user->id)->where('status', 'absent')->count();
        $lateCount = EventAttendance::where('user_id', $user->id)->where('status', 'late')->count();
        $excusedCount = EventAttendance::where('user_id', $user->id)->where('status', 'excused')->count();

        // Activity points
        $totalActivityPoints = EventAttendance::where('user_id', $user->id)
            ->where('status', 'present')
            ->with('event')
            ->get()
            ->sum(function ($attendance) {
                return $attendance->event->activity_points ?? 0;
            });

        // Calculate rates
        $approvalRate = $totalRegistrations > 0 ? round(($approvedRegistrations / $totalRegistrations) * 100, 1) : 0;
        $attendanceRate = $approvedRegistrations > 0 ? round(($totalAttendance / $approvedRegistrations) * 100, 1) : 0;
        $presentRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'registrations' => [
                    'total' => $totalRegistrations,
                    'approved' => $approvedRegistrations,
                    'pending' => $pendingRegistrations,
                    'rejected' => $rejectedRegistrations,
                    'approval_rate' => $approvalRate,
                ],
                'attendance' => [
                    'total' => $totalAttendance,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'excused' => $excusedCount,
                    'attendance_rate' => $attendanceRate,
                    'present_rate' => $presentRate,
                ],
                'activity_points' => [
                    'total' => $totalActivityPoints,
                    'average_per_event' => $presentCount > 0 ? round($totalActivityPoints / $presentCount, 1) : 0,
                ],
            ]
        ]);
    }

    // Helper methods
    private function canRegister($event, $user): bool
    {
        // Check if event is published
        if ($event->status !== 'published') {
            return false;
        }

        // Check if event hasn't started yet
        if ($event->start_date <= now()) {
            return false;
        }

        // Check if already registered
        $existingRegistration = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->exists();

        return !$existingRegistration;
    }

    private function canUnregister($event, $user): bool
    {
        $registration = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$registration) {
            return false;
        }

        // Can only unregister if status is pending or approved and event hasn't started
        return in_array($registration->status, ['pending', 'approved']) && $event->start_date > now();
    }

    private function getEventStatus($event): string
    {
        $now = now();
        
        if ($event->start_date > $now) {
            return 'upcoming';
        } elseif ($event->start_date <= $now && $event->end_date >= $now) {
            return 'ongoing';
        } else {
            return 'completed';
        }
    }

    private function getStatusLabel($status): string
    {
        return match($status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            default => $status,
        };
    }

    private function getAttendanceStatusLabel($status): string
    {
        return match($status) {
            'present' => 'Có mặt',
            'absent' => 'Vắng mặt',
            'late' => 'Đi muộn',
            'excused' => 'Có phép',
            default => $status,
        };
    }
}

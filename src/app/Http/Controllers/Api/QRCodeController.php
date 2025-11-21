<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\NotificationController;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QRCodeController extends Controller
{
    /**
     * Generate QR code token for event
     * 
     * @param Request $request
     * @param int $eventId
     * @return JsonResponse
     */
    public function generateQR(Request $request, $eventId): JsonResponse
    {
        $user = $request->user();

        // Check if user is UNION_MANAGER or ADMIN
        if (!$user->isUnionManager() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền tạo QR code'
            ], 403);
        }

        // Check if UNION_MANAGER manages this event's union
        if ($user->isUnionManager()) {
            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện không tồn tại'
                ], 404);
            }

            $userUnionIds = $user->unionManager->pluck('union_id')->toArray();
            if (!in_array($event->union_id, $userUnionIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền tạo QR code cho sự kiện này'
                ], 403);
            }
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện không tồn tại'
            ], 404);
        }

        // Check if event is ongoing or upcoming
        if ($event->end_date < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện đã kết thúc'
            ], 400);
        }

        // Generate unique token
        $token = Str::random(32);
        $expiresAt = now()->addSeconds(30); // Token expires in 30 seconds

        // Store token in cache with event info
        Cache::put("qr_token:{$token}", [
            'event_id' => $eventId,
            'issued_at' => now()->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'used' => false,
        ], $expiresAt);

        // Generate QR code data (JSON with token)
        $qrData = json_encode([
            'token' => $token,
            'event_id' => $eventId,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        // Generate QR code image
        $options = new QROptions([
            'version' => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => false, // Generate binary image
        ]);

        $qrcode = new QRCode($options);
        $qrImageBinary = $qrcode->render($qrData);

        // Store QR code image in cache
        $imageCacheKey = "qr_image:{$token}";
        Cache::put($imageCacheKey, base64_encode($qrImageBinary), $expiresAt);

        // Generate URL for QR code image
        $qrCodeUrl = route('qr-code.image', ['token' => $token]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'qr_code_url' => $qrCodeUrl, // URL to QR code image
                'expires_at' => $expiresAt->toIso8601String(),
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                ],
            ]
        ]);
    }

    /**
     * Scan QR code and mark attendance
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function scanQR(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is a student
        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ sinh viên mới có thể điểm danh'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $token = $request->input('token');

        // Get token data from cache
        $tokenData = Cache::get("qr_token:{$token}");

        if (!$tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        // Check if token is expired
        $expiresAt = \Carbon\Carbon::parse($tokenData['expires_at']);
        if ($expiresAt->isPast()) {
            Cache::forget("qr_token:{$token}");
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn'
            ], 400);
        }

        // Check if token already used (one-time use)
        if ($tokenData['used']) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã được sử dụng'
            ], 400);
        }

        $eventId = $tokenData['event_id'];
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện không tồn tại'
            ], 404);
        }

        // Check if event is ongoing or upcoming
        if ($event->end_date < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Sự kiện đã kết thúc'
            ], 400);
        }

        // Check if user has registered for this event
        $registration = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('status', 'approved')
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng ký sự kiện này hoặc đăng ký chưa được duyệt'
            ], 400);
        }

        // Check if already attended
        $existingAttendance = EventAttendance::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();

        if ($existingAttendance && $existingAttendance->status === 'present') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã điểm danh rồi'
            ], 400);
        }

        // Mark token as used
        $tokenData['used'] = true;
        Cache::put("qr_token:{$token}", $tokenData, $expiresAt);

        // Create or update attendance record
        if ($existingAttendance) {
            $existingAttendance->update([
                'status' => 'present',
                'attended_at' => now(),
                'registered_by' => $user->id, // Self-registered via QR
            ]);
            $attendance = $existingAttendance;
        } else {
            $attendance = EventAttendance::create([
                'event_id' => $eventId,
                'user_id' => $user->id,
                'status' => 'present',
                'attended_at' => now(),
                'registered_by' => $user->id,
            ]);
        }

        // Auto-add activity points if not already added
        $activityPointsEarned = 0;
        if ($event->activity_points > 0) {
            $student = $user->student;
            if ($student) {
                $currentPoints = $student->activity_points ?? 0;
                $student->update([
                    'activity_points' => $currentPoints + $event->activity_points
                ]);
                $activityPointsEarned = $event->activity_points;
            }
        }

        // Tạo thông báo điểm danh thành công
        NotificationController::createAttendanceSuccessNotification($user, $event, $activityPointsEarned);

        return response()->json([
            'success' => true,
            'message' => 'Điểm danh thành công',
            'data' => [
                'attendance' => [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'attended_at' => $attendance->attended_at,
                ],
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'activity_points' => $event->activity_points,
                ],
                'activity_points_earned' => $activityPointsEarned,
            ]
        ]);
    }

    /**
     * Serve QR code image
     * 
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function serveImage($token)
    {
        // Get QR code image from cache
        $imageCacheKey = "qr_image:{$token}";
        $qrImageBase64 = Cache::get($imageCacheKey);

        if (!$qrImageBase64) {
            abort(404, 'QR code không tồn tại hoặc đã hết hạn');
        }

        // Decode base64 image
        $qrImageBinary = base64_decode($qrImageBase64);

        // Return image response
        return response($qrImageBinary, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}

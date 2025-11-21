<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\QRCodeController;
use App\Http\Controllers\Api\UnionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // Union routes
    Route::get('unions', [UnionController::class, 'index']);

    // Student routes
    Route::prefix('student')->group(function () {
        Route::get('events', [StudentController::class, 'getEvents']);
        Route::get('events/{id}', [StudentController::class, 'getEvent']);
        Route::post('events/{id}/register', [StudentController::class, 'registerEvent']);
        Route::delete('events/{id}/unregister', [StudentController::class, 'unregisterEvent']);
        Route::get('registrations', [StudentController::class, 'getRegistrations']);
        Route::get('attendance', [StudentController::class, 'getAttendance']);
        Route::get('statistics', [StudentController::class, 'getStatistics']);
        Route::post('scan-qr', [QRCodeController::class, 'scanQR']);
    });

    // QR Code routes (for UNION_MANAGER and ADMIN)
    Route::prefix('events')->group(function () {
        Route::get('{id}/qr-code', [QRCodeController::class, 'generateQR']);
    });
});

/**
 * @OA\Get(
 *     path="/test",
 *     summary="Test API endpoint",
 *     description="Kiểm tra API có hoạt động không",
 *     tags={"Test"},
 *     @OA\Response(
 *         response=200,
 *         description="API hoạt động bình thường",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="API is working!"),
 *             @OA\Property(property="timestamp", type="string", example="2025-09-20T02:02:21.524428Z")
 *         )
 *     )
 * )
 */
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
    ]);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;

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

    // Student routes
    Route::prefix('student')->group(function () {
        Route::get('events', [StudentController::class, 'getEvents']);
        Route::get('events/{event}', [StudentController::class, 'getEvent']);
        Route::post('events/{event}/register', [StudentController::class, 'registerEvent']);
        Route::delete('events/{event}/unregister', [StudentController::class, 'unregisterEvent']);
        Route::get('registrations', [StudentController::class, 'getRegistrations']);
        Route::get('attendance', [StudentController::class, 'getAttendance']);
        Route::get('statistics', [StudentController::class, 'getStatistics']);
    });
});

// Test route
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
    ]);
});

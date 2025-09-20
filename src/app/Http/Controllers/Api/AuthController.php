<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new student
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'student_id' => 'required|string|max:20|unique:students',
            'class' => 'required|string|max:50',
            'faculty' => 'required|string|max:100',
            'course' => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'phone' => 'nullable|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->full_name,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
            ]);

            // Create student profile
            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $request->student_id,
                'class' => $request->class,
                'faculty' => $request->faculty,
                'course' => $request->course,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'phone' => $request->phone,
            ]);

            // Generate token
            $token = $user->createToken('student-api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'student' => [
                        'student_id' => $student->student_id,
                        'class' => $student->class,
                        'faculty' => $student->faculty,
                        'course' => $student->course,
                        'date_of_birth' => $student->date_of_birth,
                        'gender' => $student->gender,
                        'phone' => $student->phone,
                    ],
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng ký thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login student
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        $user = Auth::user();

        // Check if user is a student
        if ($user->role !== 'student') {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không phải là sinh viên'
            ], 403);
        }

        // Delete existing tokens
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('student-api-token')->plainTextToken;

        // Load student profile
        $student = $user->student;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'student' => $student ? [
                    'student_id' => $student->student_id,
                    'class' => $student->class,
                    'faculty' => $student->faculty,
                    'course' => $student->course,
                    'date_of_birth' => $student->date_of_birth,
                    'gender' => $student->gender,
                    'phone' => $student->phone,
                ] : null,
                'token' => $token,
            ]
        ], 200);
    }

    /**
     * Logout student
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng xuất thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ],
                'student' => $student ? [
                    'student_id' => $student->student_id,
                    'class' => $student->class,
                    'faculty' => $student->faculty,
                    'course' => $student->course,
                    'date_of_birth' => $student->date_of_birth,
                    'gender' => $student->gender,
                    'phone' => $student->phone,
                    'created_at' => $student->created_at,
                ] : null,
            ]
        ], 200);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:15',
            'class' => 'sometimes|string|max:50',
            'faculty' => 'sometimes|string|max:100',
            'course' => 'sometimes|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update user
            if ($request->has('full_name')) {
                $user->update(['full_name' => $request->full_name]);
            }

            // Update student profile
            if ($student && $request->hasAny(['phone', 'class', 'faculty', 'course'])) {
                $studentData = $request->only(['phone', 'class', 'faculty', 'course']);
                $student->update(array_filter($studentData));
            }

            // Reload relationships
            $user->refresh();
            $student = $user->student;

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'student' => $student ? [
                        'student_id' => $student->student_id,
                        'class' => $student->class,
                        'faculty' => $student->faculty,
                        'course' => $student->course,
                        'date_of_birth' => $student->date_of_birth,
                        'gender' => $student->gender,
                        'phone' => $student->phone,
                    ] : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thông tin thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng'
            ], 400);
        }

        try {
            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Revoke all tokens to force re-login
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đổi mật khẩu thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

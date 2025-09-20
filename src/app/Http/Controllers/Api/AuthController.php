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
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="CMS Đoàn Hội API",
 *     version="1.0.0",
 *     description="API documentation for CMS Đoàn Hội - Student Management System",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Đăng ký sinh viên mới",
     *     description="Tạo tài khoản sinh viên mới với thông tin đầy đủ",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name","email","password","password_confirmation","student_id","class","faculty","course","date_of_birth","gender"},
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="email", type="string", format="email", example="student@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="student_id", type="string", example="SV001"),
     *             @OA\Property(property="class", type="string", example="CNTT01"),
     *             @OA\Property(property="faculty", type="string", example="Công nghệ thông tin"),
     *             @OA\Property(property="course", type="string", example="2024"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
     *             @OA\Property(property="gender", type="string", enum={"male","female"}, example="male"),
     *             @OA\Property(property="phone", type="string", example="0123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Đăng ký thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đăng ký thành công"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                     @OA\Property(property="email", type="string", example="student@example.com"),
     *                     @OA\Property(property="role", type="string", example="student")
     *                 ),
     *                 @OA\Property(property="student", type="object",
     *                     @OA\Property(property="student_id", type="string", example="SV001"),
     *                     @OA\Property(property="class", type="string", example="CNTT01"),
     *                     @OA\Property(property="faculty", type="string", example="Công nghệ thông tin"),
     *                     @OA\Property(property="course", type="string", example="2024"),
     *                     @OA\Property(property="date_of_birth", type="string", example="2000-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="gender", type="string", example="male"),
     *                     @OA\Property(property="phone", type="string", example="0123456789")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abc123...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Đăng nhập sinh viên",
     *     description="Đăng nhập với email và mật khẩu để nhận token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="student@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đăng nhập thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đăng nhập thành công"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                     @OA\Property(property="email", type="string", example="student@example.com"),
     *                     @OA\Property(property="role", type="string", example="student")
     *                 ),
     *                 @OA\Property(property="student", type="object",
     *                     @OA\Property(property="student_id", type="string", example="SV001"),
     *                     @OA\Property(property="class", type="string", example="CNTT01"),
     *                     @OA\Property(property="faculty", type="string", example="Công nghệ thông tin"),
     *                     @OA\Property(property="course", type="string", example="2024"),
     *                     @OA\Property(property="date_of_birth", type="string", example="2000-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="gender", type="string", example="male"),
     *                     @OA\Property(property="phone", type="string", example="0123456789")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="2|abc123...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Email hoặc mật khẩu không đúng",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email hoặc mật khẩu không đúng")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Đăng xuất sinh viên",
     *     description="Đăng xuất và thu hồi token hiện tại",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Đăng xuất thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đăng xuất thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/auth/profile",
     *     summary="Lấy thông tin profile",
     *     description="Lấy thông tin profile của sinh viên hiện tại",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="full_name", type="string", example="Nguyễn Văn A"),
     *                     @OA\Property(property="email", type="string", example="student@example.com"),
     *                     @OA\Property(property="role", type="string", example="student"),
     *                     @OA\Property(property="created_at", type="string", example="2025-09-20T02:02:33.000000Z")
     *                 ),
     *                 @OA\Property(property="student", type="object",
     *                     @OA\Property(property="student_id", type="string", example="SV001"),
     *                     @OA\Property(property="class", type="string", example="CNTT01"),
     *                     @OA\Property(property="faculty", type="string", example="Công nghệ thông tin"),
     *                     @OA\Property(property="course", type="string", example="2024"),
     *                     @OA\Property(property="date_of_birth", type="string", example="2000-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="gender", type="string", example="male"),
     *                     @OA\Property(property="phone", type="string", example="0123456789"),
     *                     @OA\Property(property="created_at", type="string", example="2025-09-20T02:02:33.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/auth/profile",
     *     summary="Cập nhật thông tin profile",
     *     description="Cập nhật thông tin profile của sinh viên",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string", example="Nguyễn Văn A Updated"),
     *             @OA\Property(property="phone", type="string", example="0987654321"),
     *             @OA\Property(property="class", type="string", example="CNTT02"),
     *             @OA\Property(property="faculty", type="string", example="Công nghệ thông tin"),
     *             @OA\Property(property="course", type="string", example="2024")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cập nhật thông tin thành công"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="student", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/change-password",
     *     summary="Đổi mật khẩu",
     *     description="Đổi mật khẩu của sinh viên",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đổi mật khẩu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đổi mật khẩu thành công. Vui lòng đăng nhập lại.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mật khẩu hiện tại không đúng",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Mật khẩu hiện tại không đúng")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Union;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class UnionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/unions",
     *     summary="Lấy danh sách đoàn hội",
     *     description="Lấy danh sách tất cả các đoàn hội",
     *     tags={"Unions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lọc theo trạng thái đoàn hội (active/inactive)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách đoàn hội",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Đoàn Thanh niên"),
     *                     @OA\Property(property="description", type="string", example="Đoàn Thanh niên Cộng sản Hồ Chí Minh"),
     *                     @OA\Property(property="logo_url", type="string", example="https://example.com/logo.jpg"),
     *                     @OA\Property(property="status", type="string", example="active")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Union::query();

        // Lọc theo status nếu có
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $unions = $query->orderBy('name', 'asc')->get();

        // Transform data
        $data = $unions->map(function ($union) {
            return [
                'id' => $union->id,
                'name' => $union->name,
                'description' => $union->description,
                'logo_url' => $union->getLogoUrl(),
                'status' => $union->status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}


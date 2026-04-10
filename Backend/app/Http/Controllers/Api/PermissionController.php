<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{

    /**
     * @OA\Get(
     *     path="/permissions",
     *     summary="Get all permissions",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all permissions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="Permission_ID", type="integer"),
     *                 @OA\Property(property="Permission_Name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $permissions = DB::table('permissions')->get();
        return response()->json($permissions);
    }

    /**
     * @OA\Post(
     *     path="/permissions",
     *     summary="Create a new permission",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Permission_Name"},
     *             @OA\Property(property="Permission_Name", type="string", maxLength=255, example="view_users")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="Permission_ID", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'Permission_Name' => 'required|string|max:255'
        ]);

        $permission = DB::table('permissions')->insertGetId([
            'Permission_Name' => $request->Permission_Name,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Permission created',
            'Permission_ID' => $permission
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/permissions/{id}",
     *     summary="Get a specific permission",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Permission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission details",
     *         @OA\JsonContent(
     *             @OA\Property(property="Permission_ID", type="integer"),
     *             @OA\Property(property="Permission_Name", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $permission = DB::table('permissions')
            ->where('Permission_ID', $id)
            ->first();

        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        return response()->json($permission);
    }

    /**
     * @OA\Put(
     *     path="/permissions/{id}",
     *     summary="Update a permission",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Permission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Permission_Name"},
     *             @OA\Property(property="Permission_Name", type="string", maxLength=255, example="edit_users")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'Permission_Name' => 'required|string|max:255'
        ]);

        $updated = DB::table('permissions')
            ->where('Permission_ID', $id)
            ->update([
                'Permission_Name' => $request->Permission_Name,
                'updated_at' => now()
            ]);

        if (!$updated) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        return response()->json([
            'message' => 'Permission updated'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/permissions/{id}",
     *     summary="Delete a permission",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Permission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        DB::table('permissions')
            ->where('Permission_ID', $id)
            ->delete();

        return response()->json([
            'message' => 'Permission deleted'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/assign-permission",
     *     summary="Assign a permission to a role",
     *     tags={"Permissions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Role_ID", "Permission_ID"},
     *             @OA\Property(property="Role_ID", type="integer", example=1),
     *             @OA\Property(property="Permission_ID", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission assigned to role successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function assignPermissionToRole(Request $request)
    {

        $request->validate([
            'Role_ID' => 'required|integer|exists:roles,Role_ID',
            'Permission_ID' => 'required|integer|exists:permissions,Permission_ID'
        ]);

        DB::table('role_permissions')->insert([
            'Role_ID' => $request->Role_ID,
            'Permission_ID' => $request->Permission_ID
        ]);

        return response()->json([
            'message' => 'Permission assigned to role'
        ], 201);
    }
}

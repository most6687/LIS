<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{

    /**
     * @OA\Get(
     *     path="/roles",
     *     summary="Get all roles",
     *     tags={"Roles"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all roles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="Role_ID", type="integer"),
     *                 @OA\Property(property="Role_Name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Role::all());
    }

    /**
     * @OA\Get(
     *     path="/roles/{id}",
     *     summary="Get a specific role",
     *     tags={"Roles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role details",
     *         @OA\JsonContent(
     *             @OA\Property(property="Role_ID", type="integer"),
     *             @OA\Property(property="Role_Name", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json($role);
    }

    /**
     * @OA\Post(
     *     path="/roles",
     *     summary="Create a new role",
     *     tags={"Roles"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Role_Name"},
     *             @OA\Property(property="Role_Name", type="string", maxLength=50, example="Editor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="role",
     *                 @OA\Property(property="Role_ID", type="integer"),
     *                 @OA\Property(property="Role_Name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
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
            'Role_Name' => 'required|string|max:50'
        ]);

        $role = Role::create([
            'Role_Name' => $request->Role_Name
        ]);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/roles/{id}",
     *     summary="Update a role",
     *     tags={"Roles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Role_Name"},
     *             @OA\Property(property="Role_Name", type="string", maxLength=50, example="Editor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="role",
     *                 @OA\Property(property="Role_ID", type="integer"),
     *                 @OA\Property(property="Role_Name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
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
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }

        $request->validate([
            'Role_Name' => 'required|string|max:50'
        ]);

        $role->update([
            'Role_Name' => $request->Role_Name
        ]);

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/roles/{id}",
     *     summary="Delete a role",
     *     tags={"Roles"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'message' => 'Role not found'
            ], 404);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

}
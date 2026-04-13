<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(User::all(), 200);
    }


    /**
     * @OA\Post(
     *     path="/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreUserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
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
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Users',
            'Action' => 'Create User',
            'Description' => 'Admin created user: ' . $user->username
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }


    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
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
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update($request->validated());

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Users',
            'Action' => 'Update User',
            'Description' => 'Admin updated user: ' . $user->username
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }


    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $username = $user->username;

        $user->delete();

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Users',
            'Action' => 'Delete User',
            'Description' => 'Admin deleted user: ' . $username
        ]);

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }

}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Authenticate user and get token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="admin"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abc123"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::create([
            'User_ID' => $user->User_ID,
            'Module' => 'Auth',
            'Action' => 'Login',
            'Description' => 'User logged into the system'
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout user and revoke token",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            ActivityLog::create([
                'User_ID' => $user->User_ID,
                'Module' => 'Auth',
                'Action' => 'Logout',
                'Description' => 'User logged out'
            ]);

            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/reset-password",
     *     summary="Reset user password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6, example="newpassword"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
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
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,Email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('Email', $request->email)->first();

        $user->update([
            'password' => $request->password
        ]);

        ActivityLog::create([
            'User_ID' => $user->User_ID,
            'Module' => 'Auth',
            'Action' => 'Reset Password',
            'Description' => 'User reset their password'
        ]);

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }
}
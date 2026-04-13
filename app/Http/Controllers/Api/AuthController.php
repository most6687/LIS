<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user->id = $user->user_id;

        $token = $user->createToken('auth_token')->plainTextToken;


        ActivityLog::create([
            'User_ID' => $user->getKey(),
            'Module' => 'Auth',
            'Action' => 'Login',
            'Description' => 'User logged into the system'
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {

            ActivityLog::create([
                'User_ID' => $user->user_id,
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


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        ActivityLog::create([
            'User_ID' => $user->user_id,
            'Module' => 'Auth',
            'Action' => 'Reset Password',
            'Description' => 'User reset their password'
        ]);

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }
}

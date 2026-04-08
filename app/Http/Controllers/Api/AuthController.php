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

        $user = User::where('Username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->Password)) {
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

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,Email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('Email', $request->email)->first();

        $user->update([
            'Password' => $request->password
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
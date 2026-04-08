<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class UserController extends Controller
{

    public function index()
    {
        return response()->json(User::all(), 200);
    }


    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Users',
            'Action' => 'Create User',
            'Description' => 'Admin created user: ' . $user->Username
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }


    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update($request->validated());

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Users',
            'Action' => 'Update User',
            'Description' => 'Admin updated user: ' . $user->Username
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $username = $user->Username;

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
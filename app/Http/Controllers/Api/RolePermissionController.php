<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RolePermission;

class RolePermissionController extends Controller
{
    // ربط Permission بالـ Role
    public function assign(Request $request)
    {
        $request->validate([
            'Role_ID' => 'required|integer|exists:roles,Role_ID',
            'Permission_ID' => 'required|integer|exists:permissions,Permission_ID',
        ]);

        $rolePermission = RolePermission::create([
            'Role_ID' => $request->Role_ID,
            'Permission_ID' => $request->Permission_ID,
        ]);

        return response()->json([
            'message' => 'Permission assigned to role successfully',
            'data' => $rolePermission
        ], 201);
    }
}
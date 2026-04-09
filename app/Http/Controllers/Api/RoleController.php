<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{

    // عرض كل الرولز
    public function index()
    {
        return response()->json(Role::all());
    }

    // عرض رول واحد
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

    // إضافة رول
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

    // تعديل رول
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

    // حذف رول
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
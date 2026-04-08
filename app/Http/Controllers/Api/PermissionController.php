<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{

    // عرض كل الصلاحيات
    public function index()
    {
        $permissions = DB::table('permissions')->get();
        return response()->json($permissions);
    }

    // إضافة صلاحية جديدة
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

    // عرض صلاحية واحدة
    public function show($id)
    {
        $permission = DB::table('permissions')
            ->where('Permission_ID', $id)
            ->first();

        return response()->json($permission);
    }

    // تعديل صلاحية
    public function update(Request $request, $id)
    {
        DB::table('permissions')
            ->where('Permission_ID', $id)
            ->update([
                'Permission_Name' => $request->Permission_Name,
                'updated_at' => now()
            ]);

        return response()->json([
            'message' => 'Permission updated'
        ]);
    }

    // حذف صلاحية
    public function destroy($id)
    {
        DB::table('permissions')
            ->where('Permission_ID', $id)
            ->delete();

        return response()->json([
            'message' => 'Permission deleted'
        ]);
    }

    public function assignPermissionToRole(Request $request)
    {

        $request->validate([
            'Role_ID' => 'required',
            'Permission_ID' => 'required'
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

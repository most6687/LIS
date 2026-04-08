<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RolePermission;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        $hasPermission = RolePermission::where('Role_ID', $user->Role_ID)
            ->whereHas('permission', function ($q) use ($permission) {
                $q->where('Permission_Name', $permission);
            })->exists();

        if (!$hasPermission) {
            return response()->json([
                'message' => 'Unauthorized - Permission Required'
            ], 403);
        }

        return $next($request);
    }
}
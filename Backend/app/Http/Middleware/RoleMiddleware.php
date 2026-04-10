<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized - Please login first'
            ], 401);
        }

        if (in_array($user->Role, $roles)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Forbidden - You do not have permission to access this resource',
            'required_roles' => $roles,
            'your_role' => $user->Role
        ], 403);
    }
}
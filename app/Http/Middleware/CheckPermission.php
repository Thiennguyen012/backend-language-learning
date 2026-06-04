<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'message' => __('messages.common.unauthorized')
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Super admins have all permissions
        if ($user->is_super_admin) {
            return $next($request);
        }

        // Check if user has permission through roles
        $hasPermission = $user->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('permission_name', $permission);
        })->exists();

        if (!$hasPermission) {
            return response()->json([
                'status_code' => Response::HTTP_FORBIDDEN,
                'message' => __('messages.common.forbidden')
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

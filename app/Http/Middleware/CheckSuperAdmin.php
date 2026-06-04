<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_super_admin) {
            return response()->json([
                'status_code' => Response::HTTP_FORBIDDEN,
                'message' => __('messages.common.forbidden')
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

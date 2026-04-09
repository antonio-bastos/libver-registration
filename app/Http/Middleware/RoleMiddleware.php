<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // No user = deny
        if (!$user) {
            abort(403, 'Unauthorized: Must be authenticated.');
        }

        // If roles are specified, user must have ONE of those roles (not just be admin)
        // Admin role is NOT automatically granted for all endpoints
        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            abort(403, 'Unauthorized: Insufficient permissions.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpsRedirect
{
    /**
     * Force HTTPS redirect in production environment.
     * This middleware should only be applied in production to prevent
     * breaking local development with HTTP.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only redirect if environment is production and scheme is NOT https
        if (app()->environment('production') && !$request->secure()) {
            return redirect(
                'https://'.$request->getHost().$request->getRequestUri()
            )->status(301);
        }

        return $next($request);
    }
}

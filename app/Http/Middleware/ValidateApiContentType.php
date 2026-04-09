<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateApiContentType
{
    /**
     * Middleware to validate Content-Type headers on API endpoints.
     * Prevents parameter pollution and ensures proper content negotiation.
     */
    public function handle(Request $request, Closure $next)
    {
        // For GET requests, Content-Type is not critical but should be checked if provided
        if ($request->isMethod('GET') && $request->header('Content-Type')) {
            // GET requests should not have a body, but if they do, check the validity
            $contentType = $request->header('Content-Type');
            if (!str_contains($contentType, 'application/json') &&
                !str_contains($contentType, 'application/x-www-form-urlencoded') &&
                !str_contains($contentType, 'multipart/form-data')) {
                return response()->json(
                    ['error' => 'Invalid Content-Type header'],
                    415 // Unsupported Media Type
                );
            }
        }

        // For POST/PUT/PATCH requests, Content-Type is required
        if ($request->isMethod(['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $contentType = $request->header('Content-Type');
            if (!$contentType) {
                return response()->json(
                    ['error' => 'Content-Type header is required'],
                    400
                );
            }

            if (!str_contains($contentType, 'application/json') &&
                !str_contains($contentType, 'application/x-www-form-urlencoded') &&
                !str_contains($contentType, 'multipart/form-data')) {
                return response()->json(
                    ['error' => 'Content-Type must be JSON, form-urlencoded, or multipart/form-data'],
                    415
                );
            }
        }

        // Log suspicious patterns for security analysis
        if ($request->has('_method') || $request->has('__proto__') || $request->has('constructor')) {
            \Illuminate\Support\Facades\Log::warning('Suspicious API request pattern detected', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'path' => $request->path(),
                'parameters' => array_keys($request->all()),
            ]);
        }

        return $next($request);
    }
}

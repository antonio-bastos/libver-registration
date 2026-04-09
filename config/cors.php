<?php

/**
 * CORS Configuration
 * 
 * Controls which origins can access your API and what methods/headers are allowed.
 * This configuration is processed by the handleCors middleware.
 */

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        // Development
        env('APP_ENV') === 'local' ? '*' : null,

        // Production
        'https://events.libver.gr',
        'https://www.events.libver.gr',
    ],

    'allowed_origins_patterns' => [
        // Allow internal subdomains in production
        env('APP_ENV') === 'production' ? '#https:\/\/(.*\.)?events\.libver\.gr$#' : null,
    ],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Accept-Language',
        'Origin',
    ],

    'exposed_headers' => [
        'Content-Type',
        'X-Total-Count',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => true,

    /**
     * Normalize origins by removing protocol and www for comparison
     */
    'normalize_origins' => true,
];

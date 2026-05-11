<?php

namespace Config;

use App\Filters\JWTAuthFilter;
use App\Filters\RateLimitFilter;
use App\Filters\RoleFilter;
use CodeIgniter\Config\Filters as BaseFilters;

class Filters extends BaseFilters
{
    /**
     * Named filter aliases resolved by string in Routes.php.
     * e.g. ['filter' => 'jwt'] or ['filter' => 'role:manager']
     */
    public array $aliases = [
        'csrf'      => \CodeIgniter\Filters\CSRF::class,
        'toolbar'   => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'  => \CodeIgniter\Filters\Honeypot::class,
        'jwt'       => JWTAuthFilter::class,
        'role'      => RoleFilter::class,
        'rateLimit' => RateLimitFilter::class,
        'contentType' => \App\Filters\ContentTypeFilter::class,
    ];

    /**
     * Global filters applied to every request.
     * 'except' uses URI path patterns (no leading slash, wildcards supported).
     */
    public array $globals = [
        'before' => [
            'contentType',
            // Apply a default rate limit globally
            'rateLimit',
        ],
        'after' => [
            'rateLimit',
        ],
    ];

    /**
     * List of filter aliases that should run on any
     * HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don’t expect could bypass the filter.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on special
     * HTTP method (GET, POST, etc.) or HTTP status code.
     */
    public array $filters = [
        // Strict rate limit for authentication (5 requests per minute)
        'rateLimit' => [
            'before' => ['api/v1/auth/login', 'api/v1/auth/refresh'],
            'arguments' => ['5']
        ],
    ];
}

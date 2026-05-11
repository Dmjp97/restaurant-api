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
        'csrf'        => \CodeIgniter\Filters\CSRF::class,
        'toolbar'     => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'    => \CodeIgniter\Filters\Honeypot::class,
        'forcehttps'  => \CodeIgniter\Filters\ForceHTTPS::class,
        'pagecache'   => \CodeIgniter\Filters\PageCache::class,
        'performance' => \CodeIgniter\Filters\PerformanceMetrics::class,
        'jwt'         => JWTAuthFilter::class,
        'role'        => RoleFilter::class,
        'rateLimit'   => RateLimitFilter::class,
    ];

    /**
     * Global filters applied to every request.
     * 'except' uses URI path patterns (no leading slash, wildcards supported).
     */
    public array $globals = [
        'before' => [
            // Rate-limit every route; auth endpoints are excluded (they have their own throttling via auth failures)
            'rateLimit' => ['except' => ['api/v1/auth/login', 'api/v1/auth/refresh']],
        ],
        'after' => [
            // rateLimit after() injects X-RateLimit-* headers into every response
            'rateLimit',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}

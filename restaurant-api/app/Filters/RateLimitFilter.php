<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RateLimitFilter
 *
 * Sliding-window rate limiter backed by the cache driver (Redis in production).
 * Default: 60 requests / 60 seconds per IP.
 *
 * Headers injected in after() so they reach the client on every response,
 * including early-exit 429 returns from before().
 */
class RateLimitFilter implements FilterInterface
{
    private const MAX_REQUESTS    = 60;
    private const WINDOW_SECONDS  = 60;
    private const CACHE_KEY_PREFIX = 'rate_limit:';

    public function before(RequestInterface $request, $arguments = null)
    {
        $cache  = service('cache');
        $ip     = $request->getIPAddress();
        $key    = self::CACHE_KEY_PREFIX . $ip;
        $max    = (int) ($arguments[0] ?? self::MAX_REQUESTS);
        $window = self::WINDOW_SECONDS;

        $hits = (int) ($cache->get($key) ?? 0);

        if ($hits >= $max) {
            return response()
                ->setStatusCode(429)
                ->setHeader('Retry-After',           (string) $window)
                ->setHeader('X-RateLimit-Limit',     (string) $max)
                ->setHeader('X-RateLimit-Remaining', '0')
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Too many requests. Please slow down.',
                ]);
        }

        // Increment counter; always set TTL to the full window so it slides.
        // For a strict fixed-window this is fine; for true sliding window, use Redis INCR + EXPIRE.
        $cache->save($key, $hits + 1, $window);

        // Pass quota to after() via server globals (safe, in-process only)
        $_SERVER['RATE_LIMIT_MAX']       = $max;
        $_SERVER['RATE_LIMIT_REMAINING'] = $max - $hits - 1;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $max       = $_SERVER['RATE_LIMIT_MAX']       ?? self::MAX_REQUESTS;
        $remaining = $_SERVER['RATE_LIMIT_REMAINING'] ?? self::MAX_REQUESTS;

        $response
            ->setHeader('X-RateLimit-Limit',     (string) $max)
            ->setHeader('X-RateLimit-Remaining', (string) max(0, (int) $remaining));
    }
}

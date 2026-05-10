<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWTHandler
 *
 * Wraps firebase/php-jwt to issue and validate access + refresh token pairs.
 * Access tokens live 15 minutes; refresh tokens live 7 days.
 */
class JWTHandler
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int    $accessTtl  = 900;       // 15 min
    private int    $refreshTtl = 604800;    // 7 days

    public function __construct()
    {
        $this->secret = env('JWT_SECRET', 'change-me-in-production');
    }

    /** Issue a short-lived access token. */
    public function generateAccessToken(array $user): string
    {
        return $this->encode([
            'sub'       => $user['id'],
            'tenant_id' => $user['tenant_id'],
            'role'      => $user['role'],
            'type'      => 'access',
            'exp'       => time() + $this->accessTtl,
            'iat'       => time(),
            'jti'       => bin2hex(random_bytes(16)),
        ]);
    }

    /** Issue a long-lived refresh token. */
    public function generateRefreshToken(array $user): string
    {
        return $this->encode([
            'sub'  => $user['id'],
            'type' => 'refresh',
            'exp'  => time() + $this->refreshTtl,
            'iat'  => time(),
            'jti'  => bin2hex(random_bytes(16)),
        ]);
    }

    /** Decode and validate a token, throwing on failure. */
    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->secret, $this->algorithm));
    }

    /** Return remaining TTL in seconds (0 if expired). */
    public function ttl(string $token): int
    {
        try {
            $payload = $this->decode($token);
            return max(0, $payload->exp - time());
        } catch (\Exception) {
            return 0;
        }
    }

    private function encode(array $payload): string
    {
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }
}

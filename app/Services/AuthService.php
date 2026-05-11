<?php

namespace App\Services;

use App\Exceptions\UnauthorizedException;
use App\Libraries\JWTHandler;
use App\Models\UserModel;
use CodeIgniter\Cache\CacheInterface;

/**
 * AuthService
 *
 * Handles login, token refresh and logout.
 * Refresh tokens are stored in Redis/cache to allow revocation.
 */
class AuthService
{
    private UserModel      $userModel;
    private JWTHandler     $jwt;
    private CacheInterface $cache;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->jwt       = new JWTHandler();
        $this->cache     = service('cache');
    }

    /**
     * Validate credentials and return token pair.
     *
     * @throws UnauthorizedException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        if (!$user['is_active']) {
            throw new UnauthorizedException('Account is disabled. Contact your administrator.');
        }

        return $this->issueTokenPair($user);
    }

    /**
     * Exchange a valid refresh token for a new token pair (rotation strategy).
     *
     * @throws UnauthorizedException
     */
    public function refresh(string $refreshToken): array
    {
        try {
            $payload = $this->jwt->decode($refreshToken);
        } catch (\Exception $e) {
            throw new UnauthorizedException('Invalid or expired refresh token.');
        }

        if (($payload->type ?? '') !== 'refresh') {
            throw new UnauthorizedException('Token type mismatch.');
        }

        // Check if the token has been revoked
        if ($this->isRevoked($payload->jti)) {
            throw new UnauthorizedException('Refresh token has been revoked.');
        }

        // Revoke the old refresh token (rotation)
        $this->revokeToken($payload->jti, $payload->exp);

        $user = $this->userModel->find($payload->sub);

        if (!$user || !$user['is_active']) {
            throw new UnauthorizedException('User not found or inactive.');
        }

        return $this->issueTokenPair($user);
    }

    /**
     * Revoke the current refresh token on logout.
     */
    public function logout(string $refreshToken): void
    {
        try {
            $payload = $this->jwt->decode($refreshToken);
            $this->revokeToken($payload->jti, $payload->exp);
        } catch (\Exception) {
            // Token already invalid — logout is still successful
        }
    }

    // ──────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────

    private function issueTokenPair(array $user): array
    {
        $accessToken  = $this->jwt->generateAccessToken($user);
        $refreshToken = $this->jwt->generateRefreshToken($user);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 900,
            'user'          => [
                'id'        => $user['id'],
                'name'      => $user['name'],
                'email'     => $user['email'],
                'role'      => $user['role'],
                'tenant_id' => $user['tenant_id'],
            ],
        ];
    }

    private function isRevoked(string $jti): bool
    {
        return (bool) $this->cache->get("revoked_token:{$jti}");
    }

    private function revokeToken(string $jti, int $exp): void
    {
        $ttl = max(0, $exp - time());
        $this->cache->save("revoked_token:{$jti}", true, $ttl);
    }
}

<?php

namespace App\Libraries;

/**
 * AuthUser
 *
 * Request-scoped singleton that holds the authenticated user.
 * Injected by JWTAuthFilter; consumed by controllers and services
 * via service('auth').
 */
class AuthUser
{
    private ?array $user = null;

    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user ? (int) $this->user['id'] : null;
    }

    public function role(): ?string
    {
        return $this->user['role'] ?? null;
    }

    public function tenantId(): ?int
    {
        return $this->user ? (int) $this->user['tenant_id'] : null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->user['role'] === 'superadmin';
    }
}

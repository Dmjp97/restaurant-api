<?php

namespace Config;

use App\Libraries\AuthUser;
use CodeIgniter\Config\Services as CoreServices;

/**
 * Services
 *
 * Registers custom application services into CI4's service locator.
 * Access them with: service('auth')
 */
class Services extends CoreServices
{
    /**
     * Returns the AuthUser singleton for the current request.
     * Shared = true means CI4 returns the same instance every time.
     */
    public static function auth(bool $getShared = true): AuthUser
    {
        if ($getShared) {
            return static::getSharedInstance('auth');
        }

        return new AuthUser();
    }
}

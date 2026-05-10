<?php

/**
 * Constants for CodeIgniter Framework
 */

// Determine the current environment
define('CI_DEBUG', (ENVIRONMENT !== 'production'));
define('ENVIRONMENT', getenv('CI_ENVIRONMENT') ?: 'development');

/**
 * For backwards compatibility with CodeIgniter 4.0.0-beta.0
 */
if (!function_exists('env')) {
    /**
     * Gets a config value from environment
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case '(empty)':
                return '';
            default:
                return $value;
        }
    }
}

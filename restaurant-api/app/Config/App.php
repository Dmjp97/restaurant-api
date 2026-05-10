<?php

namespace Config;

use CodeIgniter\Config\App as BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     * URL to your CodeIgniter root. Typically this will be your base URL,
     * WITH a trailing slash:
     *
     * http://example.com/
     */
    public string $baseURL = '';

    /**
     * Allowed Hostnames in the Host header in requests if you want to
     * restrict requests to certain domains only.
     *
     * @var array<int, string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     * Typically this will be your index.php file, unless you've renamed it to
     * something else. If you are using mod_rewrite to remove the page set this
     * variable so that it is blank.
     */
    public string $indexPage = '';

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     * The protocol to determine your application URL. Typically, this will be
     * REQUEST_URI, PATH_INFO, QUERY_STRING, etc. Leave this to auto-detect.
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * --------------------------------------------------------------------------
     * Enable/Disable Query Strings
     * --------------------------------------------------------------------------
     * FALSE = Checks are skipped, but ugly URLs are generated.
     * TRUE  = Query strings simulated using mod_rewrite.
     */
    public bool $allowQueryStringUriSegments = false;

    /**
     * Environment Settings
     */
    public string $environment = ENVIRONMENT;

    /**
     * Different Session configurations for different environment types
     *
     * @var array<int, string>
     */
    public array $sessionExpiration = [
        'dev'        => (60 * 60 * 24 * 365),
        'production' => (60 * 60 * 24 * 365),
    ];

    /**
     * Session Handler: 'CodeIgniter\Session\Handlers\FileHandler'
     */
    public string $sessionHandler = 'CodeIgniter\Session\Handlers\FileHandler';

    /**
     * Session Cookie Settings
     */
    public string $sessionCookieName = 'XSESSIONID';
    public int $sessionCookieTimeout = 7200;
    public string $sessionCookieDomain = '';
    public string $sessionCookiePath = '/';
    public bool $sessionCookieSecure = false;
    public bool $sessionCookieHTTPOnly = true;
    public string $sessionCookieSameSite = 'Lax';

    /**
     * Session Database Settings
     */
    public string $sessionDBGroup = '';

    /**
     * If false, generated CSRF tokens are not saved to sessions
     */
    public bool $CSRFTokenRandomize = true;
    public string $CSRFCookieName = 'csrf_cookie_name';
    public int $CSRFCookieExpires = 7200;
    public bool $CSRFCookieSecure = false;
    public bool $CSRFCookieHTTPOnly = true;
    public string $CSRFCookieSameSite = 'Lax';
    public string $CSRFHeaderName = 'X-CSRF-TOKEN';

    /**
     * CORS Settings
     */
    public bool $enableCORS = false;
    public array $CORSAllowedOrigins = ['*'];
    public array $CORSAllowedHeaders = ['*'];
    public array $CORSAllowedMethods = ['*'];
    public int $CORSMaxAge = 7200;
    public bool $CORSExposeHeaders = false;
    public bool $CORSAllowCredentials = false;

    /**
     * Security Headers
     */
    public array $headers = [];
}

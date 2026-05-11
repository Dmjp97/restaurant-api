<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public string $baseURL = 'http://localhost/';

    public array $allowedHostnames = [];

    public string $indexPage = '';

    public string $uriProtocol = 'REQUEST_URI';

    public string $defaultLocale = 'en';

    public bool $negotiateLocale = false;

    public array $supportedLocales = ['en'];

    public string $appTimezone = 'UTC';

    public string $charset = 'UTF-8';

    public bool $forceGlobalSecureRequests = false;

    public array $proxyIPs = [];

    public bool $CSPEnabled = false;

    public function __construct()
    {
        parent::__construct();

        $baseURL = env('app.baseURL') ?: env('APP_BASE_URL') ?: env('RAILWAY_PUBLIC_DOMAIN');

        if ($baseURL) {
            $processedURL = str_starts_with($baseURL, 'http')
                ? rtrim($baseURL, '/') . '/'
                : 'https://' . rtrim($baseURL, '/') . '/';

            if (filter_var($processedURL, FILTER_VALIDATE_URL)) {
                $this->baseURL = $processedURL;
            }
        }

        $this->forceGlobalSecureRequests = filter_var(
            env('app.forceGlobalSecureRequests', env('APP_FORCE_HTTPS', false)),
            FILTER_VALIDATE_BOOL
        );
    }
}

<?php

namespace Config;

class App
{
    public string $baseURL = '';
    public array $allowedHostnames = [];
    public string $indexPage = '';
    public string $uriProtocol = 'REQUEST_URI';
    public bool $allowQueryStringUriSegments = false;
    public string $environment = 'production';
}

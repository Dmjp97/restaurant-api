<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cookie extends BaseConfig
{
    public string $prefix = '';
    public $expires = 0;
    public string $path = '/';
    public string $domain = '';
    public bool $secure = false;
    public bool $httponly = true;
    public string $samesite = 'Lax';
    public bool $raw = false;
}

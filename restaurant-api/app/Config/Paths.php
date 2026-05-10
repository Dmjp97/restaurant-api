<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Paths extends BaseConfig
{
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';
    public string $appDirectory = __DIR__ . '/..';
    public string $writableDirectory = __DIR__ . '/../../writable';
    public string $testsDirectory = __DIR__ . '/../../tests';
    public string $viewDirectory = __DIR__ . '/../../app/Views';
    public string $publicDirectory = __DIR__ . '/../../public';
}

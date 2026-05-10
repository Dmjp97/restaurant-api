<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class UserAgents extends BaseConfig
{
    public array $platforms = [];

    public array $browsers = [];

    public array $mobiles = [];

    public array $robots = [];
}

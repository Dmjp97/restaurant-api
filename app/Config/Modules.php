<?php

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

class Modules extends BaseModules
{
    public $enabled = true;

    public $discoverInComposer = true;

    public $aliases = [
        'services',
    ];

    public $composerPackages = [];
}

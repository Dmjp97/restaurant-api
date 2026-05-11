<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'      => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->default['hostname'] = env('MYSQLHOST', env('database.default.hostname', '127.0.0.1'));
        $this->default['username'] = env('MYSQLUSER', env('database.default.username', 'root'));
        $this->default['password'] = env('MYSQLPASSWORD', env('database.default.password', ''));
        $this->default['database'] = env('MYSQLDATABASE', env('database.default.database', 'restaurant_platform'));
        $this->default['port']     = (int) env('MYSQLPORT', env('database.default.port', 3306));
        $this->default['DBDriver'] = env('database.default.DBDriver', 'MySQLi');
        $this->default['DBDebug']  = ENVIRONMENT !== 'production';
    }
}

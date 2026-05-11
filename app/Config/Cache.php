<?php

namespace Config;

use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use CodeIgniter\Config\BaseConfig;

class Cache extends BaseConfig
{
    public string $handler = 'file';

    public string $backupHandler = 'file';

    public string $storePath = WRITEPATH . 'cache/';

    public string $prefix = '';

    public int $ttl = 60;

    public string $reservedCharacters = '{}()\\@';

    public array $file = [
        'storePath' => WRITEPATH . 'cache/',
        'mode'      => 0640,
    ];

    public array $redis = [
        'host'     => '127.0.0.1',
        'password' => null,
        'port'     => 6379,
        'timeout'  => 0,
        'database' => 0,
    ];

    public $cacheQueryString = false;

    public array $validHandlers = [
        'dummy'    => DummyHandler::class,
        'file'     => FileHandler::class,
        'predis'   => PredisHandler::class,
        'redis'    => RedisHandler::class,
        'wincache' => WincacheHandler::class,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->handler = env('cache.handler', env('CACHE_HANDLER', 'file'));
        $this->redis = [
            'host'     => env('REDISHOST', env('cache.redis.host', '127.0.0.1')),
            'password' => env('REDISPASSWORD', env('cache.redis.password')),
            'port'     => (int) env('REDISPORT', env('cache.redis.port', 6379)),
            'timeout'  => (float) env('cache.redis.timeout', 0),
            'database' => (int) env('REDIS_DB', env('cache.redis.database', 0)),
        ];
    }
}

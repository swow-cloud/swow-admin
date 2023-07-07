<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
return [
    'default' => [
        'driver' => \Hyperf\Support\env('DB_DRIVER', 'mysql'),
        'host' => \Hyperf\Support\env('DB_HOST', 'localhost'),
        'port' => \Hyperf\Support\env('DB_PORT', 3306),
        'database' => \Hyperf\Support\env('DB_DATABASE', 'hyperf'),
        'username' => \Hyperf\Support\env('DB_USERNAME', 'root'),
        'password' => \Hyperf\Support\env('DB_PASSWORD', ''),
        'charset' => \Hyperf\Support\env('DB_CHARSET', 'utf8mb4'),
        'collation' => \Hyperf\Support\env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => \Hyperf\Support\env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) \Hyperf\Support\env('DB_MAX_IDLE_TIME', 60),
        ],
        'cache' => [
            'handler' => Hyperf\ModelCache\Handler\RedisHandler::class,
            'cache_key' => '{mc:%s:m:%s}:%s:%s',
            'prefix' => 'default',
            'ttl' => 3600 * 24,
            'empty_model_ttl' => 600,
            'load_script' => true,
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
            ],
        ],
    ],
];

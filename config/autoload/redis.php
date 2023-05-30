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
        'host' => \Hyperf\Support\env('REDIS_HOST', 'localhost'),
        'auth' => \Hyperf\Support\env('REDIS_AUTH', null),
        'port' => (int) \Hyperf\Support\env('REDIS_PORT', 6379),
        'db' => (int) \Hyperf\Support\env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) \Hyperf\Support\env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];

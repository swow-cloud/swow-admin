<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use Swow\Debug\Debugger\Debugger;

use SwowCloud\SDB\WebSocketDebugger;
use function Hyperf\Support\env;

return [
    //    'handler' => Debugger::class,
    'handler' => WebSocketDebugger::class,
    'options' => [
        'server' => [
            'host' => '127.0.0.1',
            'port' => 9764,
        ],
        'ssl' => [
            'enable' => env('ENABLE_SSL', false),
            'certificate' => env('CERTIFICATE'),
            'certificate_key' => env('CERTIFICATE_KEY'),
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ],
];

<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use CloudAdmin\Log\AppendRequestIdWithMemoryUsageProcessor;
use CloudAdmin\Log\SwowSocketHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

use function Hyperf\Support\env;

if (env('APP_DEBUG') === true) {
    $handler = SwowSocketHandler::class;
    $constructor = [
        'level' => Level::Debug,
        'bubble' => true,
        'useLocking' => true,
    ];
} else {
    $handler = RotatingFileHandler::class;
    $constructor = [
        'filename' => BASE_PATH . '/runtime/logs/cloud.log',
        'maxFiles' => 10,
        'level' => Level::Debug,
    ];
}

return [
    'default' => [
        'handler' => [
            'class' => $handler,
            'constructor' => $constructor,
        ],
        'formatter' => [
            'class' => LineFormatter::class,
            'constructor' => [
                'format' => "%datetime% [%channel%.%level_name%]: %message% %context% %extra%\n",
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
        'processors' => [
            [
                'class' => AppendRequestIdWithMemoryUsageProcessor::class,
            ],
        ],
    ],
    'sql' => [
        'handler' => [
            'class' => RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/sql.log',
                'maxFiles' => 10,
                'level' => Level::Debug,
            ],
        ],
        'formatter' => [
            'class' => LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
        'processors' => [
            [
                'class' => AppendRequestIdWithMemoryUsageProcessor::class,
            ],
        ],
    ],
];

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
use Monolog\Level;

if (\Hyperf\Support\env('APP_DEBUG') === true) {
    $handler = SwowSocketHandler::class;
    $constructor = [
        'level' => Level::Debug,
        'bubble' => true,
        'useLocking' => true,
    ];
} else {
    $handler = Monolog\Handler\RotatingFileHandler::class;
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
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => "%datetime% [%channel%.%level_name%]: %message% %context% %extra%\n",
                'dateFormat' => 'g:i:s A',
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
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/sql.log',
                'maxFiles' => 10,
                'level' => Level::Debug,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
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

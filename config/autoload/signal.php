<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use CloudAdmin\Signal\SwowServerStopHandler;

return [
    'handlers' => [
        SwowServerStopHandler::class => PHP_INT_MIN,
    ],
    'timeout' => 5.0,
];

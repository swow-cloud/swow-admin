<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use function Hyperf\Support\env;
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
return [
    'enable' => env('ENABLE_PROFILER', false),
    'options' => [
        'flags' => XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY,
    ],
];

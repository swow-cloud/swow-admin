<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use Hyperf\Crontab\Crontab;

return [
    'enable' => true,
    // 通过配置文件定义的定时任务
    'crontab' => [
        // Closure 类型定时任务 (仅在 Coroutine style server 中支持)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('*/ * * * *')->setCallback(function () {
            \var_dump(\date('Y-m-d H:i:s'), 111111111);
        })->setEnvironments('production'),
    ],
];

<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use CloudAdmin\Casbin\Adapters\Mysql\DatabaseAdapter;
use CloudAdmin\Casbin\Watcher\CasbinWatcher;

return [
    /*
       * Casbin model setting.
       */
    'model' => [
        // Available Settings: "file", "text"
        'config_type' => 'file',
        'config_file_path' => BASE_PATH . '/config/autoload/casbin-rbac-model.conf',
        'config_text' => '',
    ],
    /*
     * Casbin adapter .
     */
    'adapter' => [
        'class' => DatabaseAdapter::class,
        'constructor' => [
            'tableName' => 'casbin_rule',
        ],
    ],
    /*
     * Casbin watcher
     */
    'watcher' => [
        'enabled' => false,
        'class' => CasbinWatcher::class,
        'constructor' => [
            'channel' => 'casbin',
        ],
    ],
    'log' => [
        'enabled' => false,
    ],
];

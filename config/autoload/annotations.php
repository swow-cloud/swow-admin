<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use Hyperf\Engine\Http\Server;
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
        ],
        'ignore_annotations' => [
            'mixin',
            ArrayShape::class,
        ],
        'class_map' => [
                        Server::class => BASE_PATH . '/cloud-admin/Server/Http/Server.php',
//            Server::class => BASE_PATH . '/cloud-admin/Server/Http2/Server.php',
        ],
    ],
];

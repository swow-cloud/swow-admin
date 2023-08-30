<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use App\Exception\Handler\AppExceptionHandler;
use App\Exception\Handler\BusinessExceptionHandler;
use App\Exception\Handler\FormValidateExceptionHandler;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;

return [
    'handler' => [
        'http' => [
            FormValidateExceptionHandler::class,
            BusinessExceptionHandler::class,
            //            HttpExceptionHandler::class,
            AppExceptionHandler::class,
        ],
    ],
];

<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Utils;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

if (! function_exists('logger')) {
    function logger(string $name = 'default'): LoggerInterface
    {
        try {
            return di()->get(LoggerFactory::class)->get($name);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
    }
}
if (! function_exists('di')) {
    function di(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
if (! function_exists('stdout')) {
    function stdout(): StdoutLoggerInterface
    {
        try {
            return di()->get(StdoutLoggerInterface::class);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
    }
}

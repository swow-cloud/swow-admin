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
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Throwable;

if (! function_exists('logger')) {
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function logger(string $name = 'default'): LoggerInterface
    {
        return di()->get(LoggerFactory::class)->get($name);
    }
}
if (! function_exists('di')) {
    function di(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
if (! function_exists('stdout')) {
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function stdout(): StdoutLoggerInterface
    {
        return di()->get(StdoutLoggerInterface::class);
    }
}

if (! function_exists('formatThrowable')) {
    /**
     * Format a throwable to string.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function formatThrowable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

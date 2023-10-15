<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
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
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Throwable;

use function function_exists;

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
     */
    function formatThrowable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('redisClient')) {
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function redisClient(string $poolName = 'default'): Redis|RedisProxy
    {
        return di()->get(RedisFactory::class)->get($poolName);
    }
}

if (! function_exists('ip')) {
    function ip(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        return $serverParams['remote_addr'] ?? '';
    }
}

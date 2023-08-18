<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\RedLock;

use CloudAdmin\RedLock\Exceptions\UnknownSetNexException;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RedisException;

class Redis
{
    public function __construct(public string $pool = 'default')
    {
    }

    public function setNex(string $key, string $value, int $expireSeconds): bool
    {
        if ($key === '' || $value === '') {
            throw new UnknownSetNexException("redis SET keyNX or value can't be empty");
        }
        try {
            return $this->getRedisCon()->set($key, $value, [
                'nx',
                'ex' => $expireSeconds,
            ]);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|RedisException $e) {
        }

        return false;
    }

    public function eval(string $src, int $keyCount, array $keysAndArgs): mixed
    {
        try {
            return $this->getRedisCon()->eval($src, $keysAndArgs, $keyCount);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|RedisException $e) {
        }

        return -1;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRedisCon(): RedisProxy
    {
        $container = ApplicationContext::getContainer();
        return $container->get(RedisFactory::class)->get($this->pool);
    }
}

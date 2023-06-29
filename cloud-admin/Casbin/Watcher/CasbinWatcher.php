<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Casbin\Watcher;

use Casbin\Persist\Watcher;
use Closure;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RedisException;

class CasbinWatcher implements Watcher
{
    /**
     * @var mixed|Redis
     */
    protected Redis $redis;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(public ContainerInterface $container, public string $channel)
    {
        $this->redis = $this->container->get(Redis::class);
    }

    public function setUpdateCallback(Closure $func): void
    {
    }

    /**
     * @throws RedisException
     */
    public function update(): void
    {
        $this->redis->publish($this->channel, 'casbin rules updated');
    }

    /**
     * @throws RedisException
     */
    public function close(): void
    {
        $this->redis->close();
    }
}

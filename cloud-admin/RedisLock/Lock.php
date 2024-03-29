<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\RedisLock;

use CloudAdmin\Interfaces\RedisLockInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as SwowCo;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisProxy;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Ramsey\Uuid\Uuid;
use RedisException;
use Swow\Coroutine;
use Throwable;

use function CloudAdmin\Utils\formatThrowable;
use function Hyperf\Support\make;
use function max;
use function sprintf;
use function usleep;

final class Lock implements RedisLockInterface
{
    /**
     * the unique id generated by the current coroutine.
     * @phpstan-param string $value
     */
    private string $value = '';

    /**
     * the lock key.
     * @phpstan-param string $key
     */
    private string $key = '';

    /**
     * the lock key's lifetime.
     * @phpstan-param int $ttl
     */
    private int $ttl;

    /**
     * @var mixed|StdoutLoggerInterface
     * @phpstan-param mixed|StdoutLoggerInterface $logger
     */
    private readonly StdoutLoggerInterface $logger;

    /**
     * redis lock config.
     */
    private array $config;

    /**
     * @phpstan-param Redis|RedisProxy $redis
     * @phpstan-param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(private readonly ContainerInterface $container, private readonly Redis|RedisProxy $redis)
    {
        if (! $this->container->has(StdoutLoggerInterface::class)) {
            throw new InvalidArgumentException('StdoutLogger not found#');
        }

        if (! ($config = $this->container->get(ConfigInterface::class)->get('redis_lock'))
        ) {
            throw new InvalidArgumentException('redis lock configuration not found#');
        }

        $this->config = $config;
        $this->logger = $this->container->get(StdoutLoggerInterface::class);
    }

    /**
     * @throws RedisException
     * @throws Throwable
     * @phpstan-param int $ttl
     * @phpstan-param string $key
     * @phpstan-return bool
     */
    public function tryLock(string $key, int $ttl = 3): bool
    {
        // 生成锁值，保证锁的唯一性
        if ($this->value === '') {
            $this->value = Uuid::uuid4()->toString();
        }

        return $this->doLock($key, $ttl);
    }

    /**
     * @throws RedisException
     * @throws Throwable
     * @phpstan-param string $key
     * @phpstan-param int $ttl
     * @phpstan-param int $retries
     * @phpstan-param int<10000, max> $usleep
     * @phpstan-return bool
     */
    public function lock(
        string $key,
        int $ttl = 3,
        int $retries = 3,
        int $usleep = 10000,
    ): bool {
        $lock = false;
        $retryTimes = max($retries, 1);

        while ($retryTimes-- > 0) {
            $this->value = Uuid::uuid4()->toString();
            $lock = $this->doLock($key, $ttl);

            if ($lock) {
                $this->logger->debug(sprintf('Lock acquired successfully, attempts: %s,Key: %s', $retryTimes, $key));
                break;
            }

            usleep($usleep);
            $this->logger->debug(sprintf('Try to acquire the lock again, the number of attempts: %s,Key: %s', $retryTimes, $key));
        }

        return $lock;
    }

    /**
     * @throws RedisException
     * @throws Throwable
     * @phpstan-return  bool
     */
    public function unLock(): bool
    {
        return (bool) $this->execLuaScript(Lua::UNLOCK, [$this->key, $this->value], 1);
    }

    /**
     * @phpstan-return  int
     */
    public function lockTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @throws Throwable
     * @phpstan-param int $ttl
     * @phpstan-return bool
     */
    public function keepAlive(int $ttl = 3): bool
    {
        try {
            $eval = $this->execLuaScript(Lua::KEEP_ALIVE, [$this->key, $ttl], 1);
            return $eval !== -2;
        } catch (Throwable $e) {
            $this->logger->error(formatThrowable($e));

            throw $e;
        }
    }

    /**
     * @throws Throwable
     * @phpstan-return bool
     */
    public function isAlive(): bool
    {
        if (! $this->key) {
            return false;
        }

        try {
            $eval = $this->redis->get($this->key);
            return $eval === $this->value;
        } catch (Throwable $e) {
            $this->logger->error(formatThrowable($e));

            throw $e;
        }
    }

    /**
     * @throws RedisException
     * @throws Throwable
     * @phpstan-param string $key
     * @phpstan-param int $ttl
     * @phpstan-return bool
     */
    private function doLock(string $key, int $ttl): bool
    {
        if (! $this->value) {
            $this->value = Uuid::uuid4()->toString();
        }

        $this->ttl = $ttl;
        $this->key = $key;

        try {
            $result = $this->execLuaScript(Lua::LOCK, [$key, $this->value, $ttl], 1);

            if ($result) {
                $this->logger->debug(sprintf('coroutine[%s] successfully hold lock[uuid:%s,key:%s], initialize the watchdog', Coroutine::getCurrent()->getId(), $this->value, $this->key));
                SwowCo::create(function () {
                    $watchdog = make(WatchDog::class);
                    $watchdog->sentinel($this, $this->config['watchDogTime'] ?? 60);
                });
            }

            return (bool) $result;
        } catch (Throwable $exception) {
            $this->logger->error(formatThrowable($exception));

            throw $exception;
        }
    }

    /**
     * @throws Throwable
     * @throws RedisException
     * @phpstan-param string $script
     * @phpstan-param array $args
     * @phpstan-param int $number
     * @phpstan-return mixed
     */
    private function execLuaScript(string $script, array $args, int $number = 0): mixed
    {
        return $this->redis->evalSha($this->redis->script('load', $script), $args, $number);
    }
}

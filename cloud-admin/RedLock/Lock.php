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

use CloudAdmin\Interfaces\LockInterface;
use CloudAdmin\RedLock\Exceptions\ErrLockException;
use CloudAdmin\RedLock\Exceptions\RuntimeException;
use CloudAdmin\RedLock\Options\LockOption;
use Exception;
use Hyperf\Engine\Coroutine;
use Swow\Channel;
use Throwable;

class Lock implements LockInterface
{
    public Channel $stopDog;

    public function __construct(
        public string $key,
        public string $token,
        public Redis $redis,
        public LockOption $lockOption,
        public int $runningDog = 0,
    ) {
        $this->stopDog = new Channel();
    }

    public function lock(): Exception|bool|null
    {
        $error = null;
        \Swow\defer(function () use (&$error) {
            if ($error === null) {
                // 加锁成功的情况下，会启动看门狗
                // 关于该锁本身是不可重入的，所以不会出现同一把锁下看门狗重复启动的情况
                $this->watchDog();
            }
        });
        // 不管是不是阻塞模式，都要先获取一次锁
        try {
            return $this->tryLock();
        } catch (Exception $exception) {
            $error = $exception;
        }
        // 非阻塞模式加锁失败直接返回错误
        if (! $this->lockOption->isBlock) {
            return $error;
        }

        if ($this->isRetryableErr($error)) {
            return $error;
        }

        return $this->blockingLock();
    }

    /**
     *  解锁. 基于 lua 脚本实现操作原子性.
     */
    public function unlock(): bool
    {
        \Swow\defer(function () {
            if ($this->stopDog->isAvailable()) {
                $this->stopDog->close();
            }
        });
        $keysAndArgs = [$this->getLockKey(), $this->token];
        $reply = $this->redis->eval(Lua::LUA_CHECK_AND_DELETE_DISTRIBUTION_LOCK, 1, $keysAndArgs);
        if ((int) $reply !== 1) {
            throw new RuntimeException('can not unlock without ownership of lock');
        }

        return true;
    }

    public function blockingLock(): bool
    {
        $blockEndTime = time() + $this->lockOption->blockWaitingSeconds;
        while (time() < $blockEndTime) {
            try {
                // 加锁成功，返回结果
                return $this->tryLock();
            } catch (Exception $exception) {
                $error = $exception;
            }
            // 不可重试类型的错误，直接返回
            if ($this->isRetryableErr($error)) {
                return false;
            }
            usleep(50);
        }
        throw new ErrLockException(sprintf('block waiting time out, err: %s', Consts::ERR_LOCK_ACQUIRED_BY_OTHERS));
    }

    public function watchDog(): void
    {
        if (! $this->lockOption->watchDogMode) {
            return;
        }
        if ($this->runningDog) {
            return;
        }
        Coroutine::create(function () {
            \Swow\defer(function () {
                $this->runningDog = 0;
            });
            $this->runWatchDog();
        });
    }

    public function runWatchDog(): void
    {
        $isStop = false;
        while (! $isStop) {
            try {
                $this->delayExpire(Consts::WATCH_DOG_WORK_STEP_SECONDS);
                sleep(Consts::WATCH_DOG_WORK_STEP_SECONDS);
                if (! $this->stopDog->isAvailable()) {
                    $isStop = true;
                }
            } catch (Throwable $exception) {
            }
        }
    }

    public function delayExpire(int $expireSeconds): void
    {
        $keysAndArgs = [$this->getLockKey(), $this->token, $expireSeconds];
        $reply = $this->redis->eval(Lua::LUA_CHECK_AND_EXPIRE_DISTRIBUTION_LOCK, 1, $keysAndArgs);
        if ((int) $reply !== 1) {
            throw new RuntimeException('can not expire lock without ownership of lock');
        }
    }

    protected function tryLock(): bool
    {
        $reply = $this->redis->setNex($this->getLockKey(), $this->token, $this->lockOption->expireSeconds);
        if ($reply === false) {
            throw new ErrLockException();
        }

        return true;
    }

    protected function getLockKey(): string
    {
        return LockOption::$redisLockKeyPrefix . $this->key;
    }

    protected function isRetryableErr($exception): bool
    {
        return $exception instanceof ErrLockException;
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\RedisLock\Options;

use CloudAdmin\RedisLock\Consts;

class LockOption
{
    public static string $redisLockKeyPrefix = 'REDIS_LOCK_PREFIX_';

    public ?bool $isBlock = false;

    public ?int $blockWaitingSeconds = 0;

    public ?int $expireSeconds = 0;

    public ?bool $watchDogMode = true;

    public function withBlock(): self
    {
        $this->isBlock = true;

        return $this;
    }

    public function withBlockWaitingSeconds(int $blockWaitingSeconds): self
    {
        $this->blockWaitingSeconds = $blockWaitingSeconds;

        return $this;
    }

    public function withExpireSeconds(int $expireSeconds): self
    {
        $this->expireSeconds = $expireSeconds;

        return $this;
    }

    public function withWatchDogMode(bool $watchDogMode): self
    {
        $this->watchDogMode = $watchDogMode;

        return $this;
    }

    public function repairLock(): void
    {
        if ($this->isBlock && $this->blockWaitingSeconds <= 0) {
            // 默认阻塞等待时间上限为 5 秒
            $this->blockWaitingSeconds = 5;
        }

        if ($this->expireSeconds > 0) {
            return;
        }
        // 用户未显式指定锁的过期时间，则此时会启动看门狗
        $this->expireSeconds = Consts::DEFAULT_LOCK_EXPIRE_SECONDS;
        $this->watchDogMode = true;
    }
}

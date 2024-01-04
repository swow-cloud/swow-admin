<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Interfaces;

use Throwable;

interface RedisLockInterface
{
    /**
     * get lock，This method will return fasle directly after the lock is failed.
     *
     * @param string $key lock unique identifier
     * @phpstan-return bool
     * @throws Throwable
     */
    public function tryLock(string $key, int $ttl = 3): bool;

    /**
     * get lock，This method will return fasle directly after the lock is failed.
     *
     * @param string $key lock unique identifier
     *
     * @param int $retries number of retries
     * @phpstan-return bool
     * @throws Throwable
     */
    public function lock(
        string $key,
        int $ttl = 3,
        int $retries = 3,
        int $usleep = 10000,
    ): bool;

    /**
     * release lock.
     * @phpstan-return bool
     * @throws Throwable
     */
    public function unLock(): bool;

    /**
     * get lock life ttl.
     * @phpstan-return int
     */
    public function lockTtl(): int;

    /**
     * Let the lock last for N seconds, the default N is 3.
     * @phpstan-return bool
     * @throws Throwable
     */
    public function keepAlive(int $ttl = 3): bool;

    /**
     * check if the lock is valid.
     * @phpstan-return bool
     * @throws Throwable
     */
    public function isAlive(): bool;
}

<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Interfaces;

interface RedisLockInterface
{
    public function get($callback = null);

    public function block($seconds, $callback = null);

    public function readLock($seconds, callable $callback = null, $interval = 250000);

    public function writeLock($seconds, callable $callback = null, $interval = 250000);

    public function release();

    public function owner(): string;

    public function forceRelease();
}

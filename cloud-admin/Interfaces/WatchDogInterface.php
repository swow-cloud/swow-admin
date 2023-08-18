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

interface WatchDogInterface
{
    /**
     * watchdog sentinel automatic renewal mechanism
     * Return true if the task completed successfully.
     *
     * @throws Throwable
     */
    public function sentinel(RedisLockInterface $lock, int $time = 60): bool;
}

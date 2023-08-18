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

class Consts
{
    // 默认的分布式锁过期时间

    public const DEFAULT_LOCK_EXPIRE_SECONDS = 30;

    // 看门狗工作时间间隙

    public const WATCH_DOG_WORK_STEP_SECONDS = 10;

    public const ERR_LOCK_ACQUIRED_BY_OTHERS = 'lock is acquired by others';
}

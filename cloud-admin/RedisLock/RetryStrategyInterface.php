<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\RedisLock;

// todo
interface RetryStrategyInterface
{
    /**
     * allows to customise the lock retry strategy.
     */
    public function nextBackOff(): int;
}

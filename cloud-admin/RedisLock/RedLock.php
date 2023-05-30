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

use CloudAdmin\RedisLock\Exceptions\RuntimeException;
use CloudAdmin\RedisLock\Options\LockOption;
use CloudAdmin\RedisLock\Options\RedLockOption;
use CloudAdmin\Utils\Os;
use Hyperf\Coroutine\Coroutine;
use SplFixedArray;

class RedLock
{
    /**
     * @param array<int,Lock> $locks
     */
    public function __construct(public array $locks, public RedLockOption $lockOption)
    {
    }

    /**
     * @param array{array{poolName:string}} $configs
     */
    public static function from(string $key, array $configs, RedLockOption $redLockOption): self
    {
        if (count($configs) < 3) {
            throw new RuntimeException('can not use redLock less than 3 nodes');
        }
        $redLockOption->repairRedLock();
        if ($redLockOption->expireDuration > 0 && count(
            $configs
        ) * $redLockOption->singleNodesTimeout * 10 > $redLockOption->expireDuration) {
            // 要求所有节点累计的超时阈值要小于分布式锁过期时间的十分之一
            throw new RuntimeException('expire thresholds of single node is too long');
        }
        $locks = new SplFixedArray(count($configs));
        foreach ($configs as $config) {
            $redis = new Redis($config['poolName']);
            $lockOption = (new LockOption())->withExpireSeconds($redLockOption->expireDuration);
            $lockOption->repairLock();
            $locks[] = new Lock($key, sprintf('%s_%s', Os::getProcessId(), Coroutine::id()), $redis, $lockOption);
        }

        return new self($locks->toArray(), $redLockOption);
    }

    public function lock(): void
    {
        $successCnt = 0;
        foreach ($this->locks as $lock) {
            $startTime = time();
            $err = $lock->lock();
            $sub = time() - $startTime;
            if ($err === true && $sub <= $this->lockOption->singleNodesTimeout) {
                ++$successCnt;
            }
        }
        if ($successCnt < count($this->locks) >> 1 + 1) {
            throw new RuntimeException('lock failed');
        }
    }

    /**
     * 解锁时，对所有节点广播解锁
     */
    public function unlock(): void
    {
        foreach ($this->locks as $lock) {
            $lock->unlock();
        }
    }
}

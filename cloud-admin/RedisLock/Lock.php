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

use Hyperf\Redis\Redis;

// todo
class Lock
{
    public Redis $redis;

    public string $key;

    public string $value;

    public int $tokenLen;

    public function obtain()
    {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function token(): string
    {
        return $this->value;
    }

    public function Metadata(): string
    {
        return '';
    }

    public function ttl(): int
    {
    }

    public function refresh(): void
    {
    }

    public function release(): void
    {
    }
}

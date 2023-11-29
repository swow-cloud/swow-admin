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

final class Lua
{
    final public const LOCK = <<<'LUA'
                local key = KEYS[1]
                local value = ARGV[1]
                local ttl = ARGV[2]

                if (redis.call('setnx', key, value) == 1) then
                    return redis.call('expire', key, ttl)
                elseif (redis.call('ttl', key) == -1) then
                    return redis.call('expire', key, ttl)
                end

                return 0
    LUA;

    final public const UNLOCK = <<<'LUA'
                local key = KEYS[1]
                local value = ARGV[1]

                if (redis.call('exists', key) == 1 and redis.call('get', key) == value)
                then
                    return redis.call('del', key)
                end

                return 0
    LUA;

    final public const KEEP_ALIVE = <<<'LUA'
                    -- get the remaining life time of the key
                    local leftoverTtl = redis.call("TTL", KEYS[1]);

                    -- never expired key
                    if (leftoverTtl == -1) then
                        return -1;
                    end;

                    -- key with remaining time
                    if (leftoverTtl ~= -2) then
                        return redis.call("EXPIRE", KEYS[1], ARGV[1]);
                    end;

                    -- key that does not exist
                    return -2;
    LUA;
}

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

class Lua
{
    final public const RELEASE_LOCK = <<<'LOCK'
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
LOCK;

    final public const SHARE_LOCK = <<<'LUA'
local mode = redis.call('hget', KEYS[1], 'mode')
if mode == false then
    local lock = redis.call('hset', KEYS[1], 'mode', ARGV[1])
    redis.call('expire', KEYS[1], ARGV[2])
    redis.call('hincrby', KEYS[1], 'lock_count', 1)
    return lock
elseif mode == ARGV[1] then
    redis.call('expire', KEYS[1], ARGV[2])
    redis.call('hincrby', KEYS[1], 'lock_count', 1)
    return 1
else
    return 0
end
LUA;

    final public const WRITE_LOCK = <<<'LUA'
local mode = redis.call('hget', KEYS[1], 'mode')
if mode == false then
    local res = redis.call('hset', KEYS[1], 'mode', ARGV[1])
    redis.call('expire', KEYS[1], ARGV[3])
    redis.call('hset', KEYS[1], 'owner', ARGV[2])
    return res
else
    return 0
end
LUA;

    final public const RELEASE_SHARE_LOCK = <<<'LUA'
local mode = redis.call('hget', KEYS[1], 'mode')
if mode == false then
    return 1
elseif mode ~= ARGV[1] then
    return 0
else
    local lock_count = redis.call('hget', KEYS[1], 'lock_count')
    if lock_count == false or tonumber(lock_count) <= 1 then
        return redis.call('del', KEYS[1])
    else
        redis.call('hincrby', KEYS[1], 'lock_count', -1)
        return 0
    end
end
LUA;

    final public const RELEASE_WRITE_LOCK = <<<'LUA'
local mode = redis.call('hget', KEYS[1], 'mode')
if mode == ARGV[1] then
    local owner = redis.call('hget', KEYS[1], 'owner')
    if owner == ARGV[2] then
        return redis.call('del', KEYS[1])
    else
        return 0
    end
else
    return 0
end
LUA;
}

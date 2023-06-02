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

//todo
class Options
{
    public string $metadata;

    public string $token;

    public function getMetadata(): string
    {
        return $this->metadata;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}

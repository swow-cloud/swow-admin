<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\RedLock\Options;

class RedLockOption
{
    /**
     * 单位ms.
     */
    public int|float $singleNodesTimeout = 10 * 1000;

    public int|float $expireDuration = 10 * 1000;

    /**
     * @return $this
     */
    public function withSingleNodesTimeout(int $singleNodesTimeout): self
    {
        $this->singleNodesTimeout = $singleNodesTimeout;

        return $this;
    }

    /**
     * @return $this
     */
    public function withRedLockExpireDuration(int $expireDuration): self
    {
        $this->expireDuration = $expireDuration;

        return $this;
    }

    public function repairRedLock(): void
    {
        if ($this->singleNodesTimeout <= 0) {
            $this->singleNodesTimeout = 50;
        }
    }
}

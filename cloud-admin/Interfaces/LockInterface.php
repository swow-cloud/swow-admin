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

interface LockInterface
{
    /**
     * @phpstan-return mixed
     */
    public function lock(): mixed;

    /**
     * @phpstan-return bool
     */
    public function unlock(): bool;
}

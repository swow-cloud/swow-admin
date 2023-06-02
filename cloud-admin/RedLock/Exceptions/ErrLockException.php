<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\RedLock\Exceptions;

use CloudAdmin\RedLock\Consts;
use RuntimeException;

class ErrLockException extends RuntimeException
{
    protected $message = Consts::ERR_LOCK_ACQUIRED_BY_OTHERS;
}

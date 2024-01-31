<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\SDB;

use BackedEnum;
use CloudAdmin\SDB\Command\HandlerInterface;

interface CommandEnumInterface extends BackedEnum
{
    /**
     * @phpstan-return  class-string<HandlerInterface>
     */
    public function handler(): string;
}

<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Http2\Frame;

use Stringable;
use ReflectionClass;

class Flag implements Stringable
{
    final public const END_STREAM = 0x01;

    final public const ACK = 0x01;

    final public const END_HEADERS = 0x04;

    final public const PADDED = 0x08;

    final public const PRIORITY = 0x20;

    public function __construct(protected int $bit)
    {
    }

    public function __toString(): string
    {
        $class = new ReflectionClass($this);
        foreach ($class->getConstants() as $name => $value) {
            if ($value == $this->bit) {
                return $name;
            }
        }
        return '';
    }

    /**
     * @return int (as hex)
     */
    public function getBit(): int
    {
        return $this->bit;
    }
}

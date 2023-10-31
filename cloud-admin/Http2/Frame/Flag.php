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

use ReflectionClass;

class Flag
{
    public const END_STREAM = 0x01;

    public const ACK = 0x01;

    public const END_HEADERS = 0x04;

    public const PADDED = 0x08;

    public const PRIORITY = 0x20;

    protected int $bit;

    public function __construct($bit)
    {
        $this->bit = $bit;
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

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

use ArrayAccess;
use LogicException;

final class Parser implements ArrayAccess
{
    public function __construct(
        public readonly array $arguments = [],
        public readonly array $options = [],
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->{$offset});
    }

    public function &offsetGet(mixed $offset): mixed
    {
        return $this->{$offset};
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Unsupported operation: setting ' . $offset);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Unsupported operation');
    }

    /**
     * @phpstan-return  array{'name':string,'arguments':array,'options':array}
     */
    public function toArray(): array
    {
        return [
            'arguments' => $this->arguments,
            'options' => $this->options,
        ];
    }
}

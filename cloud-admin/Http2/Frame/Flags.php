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

use ArrayIterator;
use CloudAdmin\Http2\Exception\InvalidFrameException;
use CloudAdmin\Http2\Parser\Http2Parser;
use Countable;
use IteratorAggregate;
use Traversable;
use function array_combine;
use function array_values;
use function count;
use function sprintf;

class Flags implements IteratorAggregate, Countable
{
    protected array $validFlags = [];

    protected array $flags;

    public function __construct(int ...$validFlags)
    {
        $this->validFlags = array_combine($validFlags, $validFlags);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->flags));
    }

    /**
     * @throws InvalidFrameException
     */
    public function add(mixed $flag): mixed
    {
        if (isset($this->validFlags[$flag])) {
            return $this->flags[$flag] = $flag;
        }
        $mag = sprintf('UnknownFrameError: Unknown frame type 0x%X received, length %d bytes', $flag, $this->validFlags);
        throw new InvalidFrameException($mag, Http2Parser::PROTOCOL_ERROR);
    }

    public function remove($flag): void
    {
        if (isset($this->flags[$flag])) {
            unset($this->flags[$flag]);
        }
    }

    public function hasFlag($flag): bool
    {
        return isset($this->flags[$flag]);
    }

    public function count(): int
    {
        return count($this->flags);
    }
}

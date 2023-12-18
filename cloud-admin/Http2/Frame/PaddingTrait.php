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

use CloudAdmin\Http2\Exception\InvalidFrameException;
use CloudAdmin\Http2\Parser\Http2Parser;
use function pack;
use function substr;
use function unpack;

trait PaddingTrait
{
    protected int $paddingLength = 0;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->paddingLength = (int) ($options['paddingLength'] ?? 0);
    }

    public function setPaddingLength(int $paddingLength): void
    {
        $this->paddingLength = $paddingLength;
    }

    public function getPaddingLength(): int
    {
        return $this->paddingLength;
    }

    protected function serializePaddingData(): string
    {
        if ($this->flags->hasFlag(Flag::PADDED)) {
            return pack('C', $this->paddingLength);
        }
        return '';
    }

    /**
     * @throws InvalidFrameException
     */
    protected function parsePaddingData(string $data): int
    {
        if ($this->flags->hasFlag(Flag::PADDED)) {
            if (! $unpack = @unpack('Cpadding_length', substr($data, 0, 1))) {
                throw new InvalidFrameException('Padding greater than length', Http2Parser::PROTOCOL_ERROR);
            }
            $this->paddingLength = $unpack['paddingLength'];
            return static::IS_PADDED;
        }
        return static::IS_NOT_PADDED;
    }
}

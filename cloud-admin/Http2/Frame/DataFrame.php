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
use function str_repeat;
use function strlen;
use function substr;

class DataFrame extends Frame implements PaddingInterface
{
    use PaddingTrait;

    protected array $definedFlags = [
        Flag::END_STREAM,
        Flag::PADDED,
    ];

    protected int $type = 0x0;

    protected int $streamAssociation = self::HAS_STREAM;

    protected mixed $data;

    /**
     * DataFrame constructor.
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->data = $options['data'] ?? '';
        $isPadded = $this->getFlags()->hasFlag(Flag::PADDED);
        $frameLength = $this->getLength();
        $headerLength = $isPadded ? 1 : 0;
        if ($frameLength < $headerLength) {
            throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
        }
    }

    public function serializeBody(): string
    {
        $padding_data = $this->serializePaddingData();
        $padding = str_repeat("\0", $this->paddingLength ?? 0);
        return $padding_data . $this->data . $padding;
    }

    /**
     * @throws InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        $padding_data_length = $this->parsePaddingData($data);
        $this->data = $data;
        if ($this->paddingLength) {
            $this->data = substr($data, $padding_data_length, $this->paddingLength * -1);
        }
        $this->bodyLength = strlen($data);
        if ($this->paddingLength && $this->paddingLength >= $this->bodyLength) {
            throw new InvalidFrameException('Padding greater than length', Http2Parser::PROTOCOL_ERROR);
        }
    }

    public function flowControlledLength(): int
    {
        $padding_len = ($this->paddingLength) ? $this->paddingLength + 1 : 0;
        return strlen($this->data) + $padding_len;
    }

    public function getData(): int|string
    {
        return $this->data;
    }

    /**
     * @return DataFrame
     */
    public function setData(int $data): static
    {
        $this->data = $data;
        return $this;
    }
}

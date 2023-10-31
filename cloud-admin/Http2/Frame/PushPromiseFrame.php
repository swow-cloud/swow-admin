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
use function str_repeat;
use function strlen;
use function substr;
use function unpack;

class PushPromiseFrame extends Frame implements PaddingInterface
{
    use PaddingTrait;

    protected array $definedFlags = [
        Flag::END_HEADERS,
        Flag::PADDED,
    ];

    protected int $type = 0x05;

    protected int $streamAssociation = self::HAS_STREAM;

    protected int $promisedStreamId;

    protected mixed $data;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->promisedStreamId = (int) ($options['promisedStreamId'] ?? null);
        $this->data = $options['data'] ?? 0;
        $isPadded = $this->getFlags()->hasFlag(Flag::PADDED);
        $headerLength = $isPadded ? 5 : 4;
        $frameLength = $this->getLength();
        if ($frameLength < $headerLength) {
            throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
        }
        $padding = $this->getPaddingLength();
        if ($frameLength - $headerLength - $padding < 0) {
            throw new InvalidFrameException('Padding greater than length', Http2Parser::PROTOCOL_ERROR);
        }
    }

    public function serializeBody(): string
    {
        $padding_data = $this->serializePaddingData();
        $padding = '';
        if ($this->paddingLength) {
            $padding = str_repeat("\0", $this->paddingLength);
        }
        $data = pack('N', $this->promisedStreamId);
        return $padding_data . $data . $this->data . $padding;
    }

    /**
     * @throws InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        $padding_data_length = $this->parsePaddingData($data);
        if (! $unpack = @unpack('Npromised_stream_id', substr($data, $padding_data_length, $padding_data_length + 4))) {
            throw new InvalidFrameException('Invalid PUSH_PROMISE body', Http2Parser::PROTOCOL_ERROR);
        }
        $this->promisedStreamId = $unpack['promisedStreamId'];
        $this->data = substr($data, $padding_data_length + 4);
        $this->bodyLength = strlen($data);
        if ($this->paddingLength && $this->paddingLength > $this->bodyLength) {
            throw new InvalidFrameException('Padding greater than length', Http2Parser::PROTOCOL_ERROR);
        }
    }

    public function getPromisedStreamId(): int
    {
        return $this->promisedStreamId;
    }

    /**
     * @return $this
     */
    public function setPromisedStreamId(int $promisedStreamId): static
    {
        $this->promisedStreamId = $promisedStreamId;
        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return $this
     */
    public function setData(string $data): static
    {
        $this->data = $data;
        return $this;
    }
}

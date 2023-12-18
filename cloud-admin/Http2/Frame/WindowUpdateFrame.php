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

use CloudAdmin\Http2\Exception\Http2StreamException;
use CloudAdmin\Http2\Exception\InvalidFrameException;
use CloudAdmin\Http2\Parser\Http2Parser;
use function pack;
use function strlen;
use function unpack;

class WindowUpdateFrame extends Frame
{
    protected array $definedFlags = [];

    protected int $type = 0x08;

    protected int $streamAssociation = self::EITHER_STREAM;

    protected mixed $windowIncrement;

    /**
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->windowIncrement = $options['windowIncrement'] ?? 0;
    }

    public function serializeBody(): string
    {
        return pack('N', $this->windowIncrement & 0x7FFFFFFF);
    }

    /**
     * @throws Http2StreamException|InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        if (! $unpack = @unpack('NwindowIncrement', $data)) {
            throw new InvalidFrameException('Invalid WINDOW_UPDATE body', Http2Parser::PROTOCOL_ERROR);
        }
        $this->windowIncrement = $unpack['windowIncrement'];
        $this->bodyLength = strlen($data);
        if ($this->bodyLength !== 4) {
            throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
        }
        $windowSize = $this->getWindowIncrement();
        if ($windowSize === 0) {
            if ($this->getStreamId()) {
                throw new Http2StreamException('Invalid zero window update value', $this->getStreamId(), Http2Parser::PROTOCOL_ERROR);
            }
            throw new InvalidFrameException('Invalid zero window update value', Http2Parser::PROTOCOL_ERROR);
        }
    }

    /**
     * @param int|mixed $windowIncrement
     * @return WindowUpdateFrame
     */
    public function setWindowIncrement(mixed $windowIncrement): static
    {
        $this->windowIncrement = $windowIncrement;
        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getWindowIncrement(): mixed
    {
        return $this->windowIncrement;
    }
}

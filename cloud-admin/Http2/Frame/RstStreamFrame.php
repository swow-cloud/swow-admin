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
use function strlen;
use function unpack;

class RstStreamFrame extends Frame
{
    protected array $definedFlags = [];

    protected int $type = 0x03;

    protected int $streamAssociation = self::HAS_STREAM;

    protected int $errorCode;

    /**
     * RstStreamFrame constructor.
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->errorCode = (int) ($options['errorCode'] ?? null);
        if ($this->getLength() != 4) {
            throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
        }
    }

    public function serializeBody(): string
    {
        return pack('N', $this->errorCode);
    }

    /**
     * @throws InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        if (strlen($data) != 4) {
            throw new InvalidFrameException('RST_STREAM must have 4 byte body', Http2Parser::PROTOCOL_ERROR);
        }
        if (! $unpack = @unpack('Nerror_code', $data)) {
            throw new InvalidFrameException('Invalid RST_STREAM body', Http2Parser::PROTOCOL_ERROR);
        }
        $this->errorCode = $unpack['errorCode'];
        $this->bodyLength = strlen($data);
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return RstStreamFrame
     */
    public function setErrorCode(int $errorCode): static
    {
        $this->errorCode = $errorCode;
        return $this;
    }
}

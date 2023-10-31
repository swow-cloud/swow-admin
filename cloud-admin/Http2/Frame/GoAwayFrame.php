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
use function substr;
use function unpack;

class GoAwayFrame extends Frame
{
    protected array $definedFlags = [];

    protected int $type = 0x07;

    protected int $streamAssociation = self::NO_STREAM;

    protected int $lastStreamId;

    protected int $errorCode;

    protected int|string $additionalData;

    protected int $bodyLen;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->lastStreamId = (int) ($options['lastStreamId'] ?? 0);
        $this->errorCode = (int) ($options['errorCode'] ?? 0);
        $this->additionalData = (int) ($options['additionalData'] ?? '');
    }

    public function serializeBody(): string
    {
        $data = pack('NN', $this->lastStreamId & 0x7FFFFFFF, $this->errorCode);
        $data .= $this->additionalData;
        return $data;
    }

    /**
     * @throws InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        if (! $unpack = @unpack('Nlast_stream_id/Nerror_code', substr($data, 0, 8))) {
            throw new InvalidFrameException('Invalid GOAWAY body.', Http2Parser::PROTOCOL_ERROR);
        }
        $this->lastStreamId = $unpack['lastStreamId'];
        $this->errorCode = $unpack['errorCode'];
        $this->bodyLen = strlen($data);
        if ($this->bodyLen < 8) {
            throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
        }
        if (strlen($data) > 8) {
            $this->additionalData = substr($data, 8);
        }
    }

    /**
     * @return GoAwayFrame
     */
    public function setLastStreamId(int $lastStreamId): static
    {
        $this->lastStreamId = $lastStreamId;
        return $this;
    }

    public function getLastStreamId(): int
    {
        return $this->lastStreamId;
    }

    /**
     * @return GoAwayFrame
     */
    public function setErrorCode(int $errorCode): static
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return GoAwayFrame
     */
    public function setAdditionalData(int $additionalData): static
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    public function getAdditionalData(): int|string
    {
        return $this->additionalData;
    }
}

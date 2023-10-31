<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace SwowCloud\Http2\Frame;

use SwowCloud\Http2\Exception\InvalidFrameException;

use function strlen;

class PriorityFrame extends Frame implements PriorityInterface
{
    use PriorityTrait;

    protected array $definedFlags = [];

    protected int $type = 0x02;

    protected int $streamAssociation = self::HAS_STREAM;

    /**
     * PingFrame constructor.
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $frameLength = $this->getLength();
        if ($frameLength !== 5) {
            throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
        }
        $parent = $this->getDependsOn();
        if ($parent & 0x80000000) {
            $parent &= 0x7FFFFFFF;
        }
        $streamId = $this->getStreamId();
        if ($parent === $streamId) {
            throw new InvalidFrameException("Invalid recursive dependency for stream {$streamId}", Http2Parser::PROTOCOL_ERROR);
        }
    }

    public function serializeBody(): string
    {
        return $this->serializePriorityData();
    }

    public function parseBody(string $data): void
    {
        $this->parsePriorityData($data);
        $this->bodyLength = strlen($data);
    }
}

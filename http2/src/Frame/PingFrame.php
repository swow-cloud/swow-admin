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
use SwowCloud\Http2\Parser\Http2Parser;

use function str_pad;
use function strlen;

class PingFrame extends Frame
{
    protected array $definedFlags = [Flag::ACK];

    protected int $type = 0x06;

    protected int $streamAssociation = self::NO_STREAM;

    protected mixed $opaqueData;

    /**
     * PingFrame constructor.
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->opaqueData = $options['opaqueData'] ?? '';
    }

    /**
     * @throws InvalidFrameException
     */
    public function serializeBody(): string
    {
        if (strlen($this->opaqueData) > 8) {
            throw new InvalidFrameException('PING frame may not have more than 8 bytes of data', Http2Parser::PROTOCOL_ERROR);
        }
        $data = $this->opaqueData;
        return str_pad($data, 8, "\x00", STR_PAD_RIGHT);
    }

    /**
     * @throws InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        if (strlen($data) != 8) {
            throw new InvalidFrameException('PING frame must have 8 byte length', Http2Parser::PROTOCOL_ERROR);
        }
        $this->opaqueData = $data;
        $this->bodyLength = strlen($data);
    }

    public function getOpaqueData(): string
    {
        return $this->opaqueData;
    }

    /**
     * @return $this
     */
    public function setOpaqueData(string $opaqueData): static
    {
        $this->opaqueData = $opaqueData;
        return $this;
    }
}

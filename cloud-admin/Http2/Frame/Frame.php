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
use Exception;
use function array_key_exists;
use function bin2hex;
use function implode;
use function in_array;
use function pack;
use function sprintf;
use function strlen;
use function strrchr;
use function substr;
use function unpack;

abstract class Frame
{
    final public const FRAMES = [
        0x00 => 'DataFrame',
        0x01 => 'HeadersFrame',
        0x02 => 'PriorityFrame',
        0x03 => 'RstStreamFrame',
        0x04 => 'SettingsFrame',
        0x05 => 'PushPromiseFrame',
        0x06 => 'PingFrame',
        0x07 => 'GoAwayFrame',
        0x08 => 'WindowUpdateFrame',
        0x09 => 'ContinuationFrame',
        0xA => 'AltSvcFrame',
    ];

    public const HAS_STREAM = 1;

    public const NO_STREAM = 2;

    public const EITHER_STREAM = 4;

    protected array $definedFlags = [];

    protected Flags $flags;

    protected int $type;

    /**
     * @var int 帧长度
     */
    protected mixed $length;

    /**
     * 流id是否为0或者非0.
     */
    protected int $streamAssociation;

    /**
     * @var int
     */
    protected mixed $streamId = 0;

    protected string $body;

    protected int $bodyLength = 0;

    /**
     * Frame constructor.
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        $this->streamId = $options['streamId'] ?? 0;
        $this->length = $options['length'] ?? 0;
        $this->flags = new Flags(...$this->definedFlags);
        foreach ($options['flags'] ?? [] as $flag) {
            $this->flags->add($flag);
        }
        if ($this->streamAssociation === self::HAS_STREAM && ! $this->streamId) {
            throw new InvalidFrameException('Invalid Frame Flag', Http2Parser::PROTOCOL_ERROR);
        }
        if ($this->streamAssociation === self::NO_STREAM && $this->streamId) {
            throw new InvalidFrameException('Invalid Frame Flag', Http2Parser::PROTOCOL_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    public function __debugInfo()
    {
        $flags = 'None';
        if ($f = $this->flags->getIterator()) {
            $flags = implode(', ', $f);
        }
        $body = bin2hex($this->serializeBody());
        if (strlen($body) > 20) {
            $body = substr($body, 0, 20) . '...';
        }
        return [sprintf(
            '%s(Stream: %s; Flags: %s): %s',
            substr(strrchr(static::class, '\\'), 1),
            $this->streamId ?? 'None',
            $flags,
            $body
        )];
    }

    /**
     * @throws InvalidFrameException
     */
    public static function parseFrame(string $data): Frame
    {
        $frame = static::parseFrameHeader(substr($data, 0, 9));
        $length = $frame->getLength();
        $frame->parseBody($data);
        return $frame;
    }

    /**
     * @throws InvalidFrameException
     */
    public static function parseFrameHeader(string $header): Frame
    {
        if (! $fields = @unpack('nlength8/Clength16/Ctype/Cflags/Nstream_id', $header)) {
            throw new InvalidFrameException('Invalid Frame Header', Http2Parser::PROTOCOL_ERROR);
        }
        $length = ($fields['length8'] << 8) + $fields['length16'];
        $type = $fields['type'];
        $flags = $fields['flags'];
        $stream_id = $fields['streamId'];
        if (! array_key_exists($type, static::FRAMES)) {
            throw new InvalidFrameException('Invalid Frame Header', Http2Parser::PROTOCOL_ERROR);
        }
        $frame = '\frame\\' . static::FRAMES[$type];
        $frame = new $frame(['streamId' => $stream_id, 'length' => $length]);
        $frame->parseFlags($flags);

        return $frame;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * the frame.
     */
    public function serialize(): string
    {
        $body = $this->serializeBody();
        $this->bodyLength = strlen($body);
        $flags = 0;
        foreach ($this->definedFlags as $flag) {
            if ($this->flags->hasFlag($flag)) {
                $flags |= $flag;
            }
        }
        $header = pack(
            'nCCCN',
            ($this->bodyLength & 0xFFFF00) >> 8,    // Length spread over top 24 bits
            $this->bodyLength & 0x0000FF,
            $this->type,
            $flags,
            $this->streamId & 0x7FFFFFFF   // Stream ID is 32 bits.
        );
        return $header . $body;
    }

    public function parseFlags(int $flag_byte): Flags
    {
        foreach ($this->definedFlags as $flag) {
            if ($flag_byte & $flag) {
                $this->flags->add($flag);
            }
        }
        return $this->flags;
    }

    abstract public function serializeBody(): string;

    abstract public function parseBody(string $data);

    /**
     * @return Flags
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @return $this
     * @throws InvalidFrameException
     */
    public function setFlags(array $flags): static
    {
        foreach ($flags as $flag) {
            if (in_array($flag, $this->definedFlags)) {
                $this->flags->add($flag);
            }
        }

        return $this;
    }

    public function getBodyLength(): int
    {
        return $this->bodyLength;
    }

    /**
     * @return Frame
     */
    public function setStreamId(int $streamId): static
    {
        $this->streamId = $streamId;
        return $this;
    }

    public function getStreamId(): int
    {
        return $this->streamId;
    }
}

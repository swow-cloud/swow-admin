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

class HeadersFrame extends Frame implements PaddingInterface, PriorityInterface
{
    use PaddingTrait;
    use PriorityTrait;

    protected array $definedFlags = [
        Flag::END_STREAM,
        Flag::END_HEADERS,
        Flag::PADDED,
        Flag::PRIORITY,
    ];

    protected int $type = 0x01;

    protected int $streamAssociation = self::HAS_STREAM;

    protected mixed $data;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->data = $options['data'] ?? '';
        $headerLength = 0;
        $isPadded = $this->getFlags()->hasFlag(Flag::PADDED);
        $isPriority = $this->getFlags()->hasFlag(Flag::PRIORITY); // 优先级权重
        if ($isPadded) { // 是否填充
            ++$headerLength;
        }
        if ($isPriority) {
            $headerLength += 5;
        }
        $frameLength = $this->getLength();
        if ($frameLength < $headerLength) {
            throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
        }
        if ($isPriority) {
            $parent = $this->getDependsOn();
            $parent &= 0x7FFFFFFF;
            if ($parent === $this->streamId) {
                throw new InvalidFrameException(
                    "Invalid recursive dependency for stream {$this->streamId}",
                    Http2Parser::PROTOCOL_ERROR
                );
            }
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
        $priority_data = '';
        if ($this->flags->hasFlag(Flag::PRIORITY)) {
            $priority_data = $this->serializePriorityData();
        }
        return $padding_data . $priority_data . $this->data . $padding;
    }

    /**
     * @throws InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        $padding_data_length = $this->parsePaddingData($data);
        $data = substr($data, $padding_data_length);
        $priority_data_length = 0;
        if ($this->flags->hasFlag(Flag::PRIORITY)) {
            $priority_data_length = $this->parsePriorityData($data);
        }
        $this->bodyLength = strlen($data);
        $this->data = substr($data, $priority_data_length, strlen($data) - $this->paddingLength);
        if ($this->paddingLength && $this->paddingLength >= $this->bodyLength) {
            throw new InvalidFrameException('Padding greater than length', Http2Parser::PROTOCOL_ERROR);
        }
    }

    /**
     * @param mixed|string $data
     * @return HeadersFrame
     */
    public function setData(mixed $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getData(): mixed
    {
        return $this->data;
    }
}

<?php
declare(strict_types=1);

namespace CloudAdmin\Http2\Frame;

use CloudAdmin\Http2\Exception\InvalidFrameException;

class ContinuationFrame extends Frame
{
    protected array $definedFlags = [Flag::END_HEADERS];
    protected int $type = 0x09;
    protected int $streamAssociation = self::HAS_STREAM;
    protected mixed $data;

    /**
     * @param array $options
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->data = $options['data'] ?? '';
    }

    /**
     * @return string
     */
    public function serializeBody(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return void
     */
    public function parseBody(string $data): void
    {
        $this->data = $data;
        $this->bodyLength = strlen($data);
    }

    /**
     * @param mixed|string $data
     * @return ContinuationFrame
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

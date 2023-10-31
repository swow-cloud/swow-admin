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

use function implode;
use function pack;
use function range;
use function strlen;
use function substr;
use function unpack;

class SettingsFrame extends Frame
{
    public const HEADER_TABLE_SIZE = 0x01;

    public const ENABLE_PUSH = 0x02;

    public const MAX_CONCURRENT_STREAMS = 0x03;

    public const INITIAL_WINDOW_SIZE = 0x04;

    public const MAX_FRAME_SIZE = 0x05;

    public const MAX_HEADER_LIST_SIZE = 0x06;

    protected array $definedFlags = [Flag::ACK];

    protected int $type = 0x04;

    protected int $streamAssociation = self::NO_STREAM;

    protected mixed $settings;

    /**
     * SettingsFrame constructor.
     * @throws InvalidFrameException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $options['settings'] = $options['settings'] ?? [];
        if ($options['settings'] && $this->flags->hasFlag(Flag::ACK)) {
            throw new InvalidFrameException('Settings must be empty if ACK flag is set', Http2Parser::PROTOCOL_ERROR);
        }
        $this->settings = $options['settings'];
    }

    public function serializeBody(): string
    {
        $settings = [];
        foreach ($this->settings as $setting => $value) {
            $settings[] = pack('nN', $setting & 0xFF, $value);
        }
        return implode('', $settings);
    }

    /**
     * @throws InvalidFrameException
     */
    public function parseBody(string $data): void
    {
        if (strlen($data) > 0) {
            $frameLength = $this->getLength();
            if ($frameLength % 6 !== 0) {
                throw new InvalidFrameException('Invalid frame length', Http2Parser::PROTOCOL_ERROR);
            }
            if ($frameLength > 60) {
                throw new InvalidFrameException('Excessive SETTINGS frame', Http2Parser::PROTOCOL_ERROR);
            }
            foreach (range(0, strlen($data) - 1, 6) as $i) {
                if (! $unpack = @unpack('nname/Nvalue', substr($data, $i, $i + 6))) {
                    throw new InvalidFrameException('Invalid SETTINGS body', Http2Parser::PROTOCOL_ERROR);
                }
                $name = $unpack['name'];
                $value = $unpack['value'];
                $this->settings[$name] = $value;
            }
        }
        $this->bodyLength = strlen($data);
    }

    /**
     * @return array|mixed
     */
    public function getSettings(): mixed
    {
        return $this->settings;
    }

    /**
     * @param array|mixed $settings
     * @return SettingsFrame
     */
    public function setSettings(mixed $settings): static
    {
        $this->settings = $settings;
        return $this;
    }
}

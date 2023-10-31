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

use function pack;
use function substr;
use function unpack;

trait PriorityTrait
{
    protected int $dependsOn;

    protected int $streamWeight;

    protected bool $exclusive;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->dependsOn = (int) ($options['dependsOn'] ?? 0);
        $this->streamWeight = (int) ($options['streamWeight'] ?? 0);
        $this->exclusive = (bool) ($options['exclusive'] ?? false);
    }

    public function getExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * @return PriorityTrait
     */
    public function setExclusive(bool $exclusive): static
    {
        $this->exclusive = $exclusive;
        return $this;
    }

    public function getDependsOn(): int
    {
        return $this->dependsOn;
    }

    /**
     * @return PriorityTrait
     */
    public function setDependsOn(int $dependsOn): static
    {
        $this->dependsOn = $dependsOn;
        return $this;
    }

    public function getStreamWeight(): int
    {
        return $this->streamWeight;
    }

    /**
     * @return PriorityTrait
     */
    public function setStreamWeight(int $streamWeight): static
    {
        $this->streamWeight = $streamWeight;
        return $this;
    }

    protected function serializePriorityData(): string
    {
        return pack(
            'NC',
            $this->dependsOn | ((int) $this->exclusive << 31),
            $this->streamWeight
        );
    }

    /**
     * @throws InvalidFrameException
     */
    protected function parsePriorityData(string $data): int
    {
        if ($unpack = @unpack('Ndepends_on/Cstream_weight', substr($data, 0, 5))) {
            $this->dependsOn = $unpack['dependsOn'];
            $this->streamWeight = $unpack['streamWeight'];
            $this->exclusive = (bool) ($this->dependsOn & PriorityInterface::MASK);
            $this->dependsOn &= ~PriorityInterface::MASK;
            return 5;
        }
        throw new InvalidFrameException('Invalid Priority data', Http2Parser::PROTOCOL_ERROR);
    }
}

<?php

declare(strict_types=1);

namespace CloudAdmin\Lru;

final class LRUMemoizer
{
    public const CAPACITY_DEFAULT = 100;

    /**
     * @var array<string, mixed>
     */
    private array $itemsByKey = [];

    /**
     * @param positive-int $capacity
     */
    public function __construct(
        private readonly int $capacity = self::CAPACITY_DEFAULT,
    ) {
    }

    /**
     * @template T
     * @param callable(): T $factory
     * @return T
     */
    public function get(string $key, callable $factory): mixed
    {
        if (\array_key_exists($key, $this->itemsByKey)) {
            /** @var T */
            $value = $this->itemsByKey[$key];
            unset($this->itemsByKey[$key]);
            $this->itemsByKey[$key] = $value;

            return $value;
        }

        $value = $factory();
        $this->itemsByKey[$key] = $value;

        if (\count($this->itemsByKey) > $this->capacity) {
            array_shift($this->itemsByKey);
        }

        return $value;
    }

    public function prune(): void
    {
        $this->itemsByKey = [];
    }
}
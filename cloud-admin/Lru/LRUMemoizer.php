<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Lru;

use function array_key_exists;
use function array_shift;
use function count;

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
    ) {}

    /**
     * @template T
     * @param callable(): T $factory
     * @return T
     * @phpstan-return T
     * @phpstan-param callable(): T $factory
     */
    public function get(string $key, callable $factory): mixed
    {
        if (array_key_exists($key, $this->itemsByKey)) {
            /** @var T */
            $value = $this->itemsByKey[$key];
            unset($this->itemsByKey[$key]);
            $this->itemsByKey[$key] = $value;

            return $value;
        }

        $value = $factory();
        $this->itemsByKey[$key] = $value;

        if (count($this->itemsByKey) > $this->capacity) {
            array_shift($this->itemsByKey);
        }

        return $value;
    }

    public function prune(): void
    {
        $this->itemsByKey = [];
    }
}

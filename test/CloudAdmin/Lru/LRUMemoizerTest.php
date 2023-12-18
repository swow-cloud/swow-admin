<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Test\CloudAdmin\Lru;

use CloudAdmin\Lru\LRUMemoizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class LRUMemoizerTest extends TestCase
{
    public function testItSavesValue(): void
    {
        $memoizer = new LRUMemoizer();
        $memoizer->get('a', static fn (): string => 'a1');

        $a = self::viewMemoizedValue($memoizer, 'a', static fn (): string => 'a2');

        self::assertSame($a, 'a1');
    }

    public function testItPrunes(): void
    {
        $memoizer = new LRUMemoizer();
        $memoizer->get('a', static fn (): string => 'a1');

        $memoizer->prune();
        $a = self::viewMemoizedValue($memoizer, 'a', static fn (): string => 'a2');

        self::assertSame($a, 'a2');
    }

    public function testItTakesCapacityIntoAccount(): void
    {
        $memoizer = new LRUMemoizer(capacity: 2);
        $memoizer->get('a', static fn (): string => 'a1');
        $memoizer->get('b', static fn (): string => 'b1');
        $memoizer->get('c', static fn (): string => 'c1');

        $a = self::viewMemoizedValue($memoizer, 'a', static fn (): string => 'a2');
        $b = self::viewMemoizedValue($memoizer, 'b', static fn (): string => 'b2');
        $c = self::viewMemoizedValue($memoizer, 'c', static fn (): string => 'c2');

        self::assertSame($a, 'a2');
        self::assertSame($b, 'b1');
        self::assertSame($c, 'c1');
    }

    public function testItRemovesLeastRecentlyUsed(): void
    {
        $memoizer = new LRUMemoizer(capacity: 2);
        $memoizer->get('a', static fn (): string => 'a1');
        $memoizer->get('b', static fn (): string => 'b1');
        $memoizer->get('a', static fn (): string => 'a1');
        $memoizer->get('c', static fn (): string => 'c1');

        $a = self::viewMemoizedValue($memoizer, 'a', static fn (): string => 'a2');
        $b = self::viewMemoizedValue($memoizer, 'b', static fn (): string => 'b2');
        $c = self::viewMemoizedValue($memoizer, 'c', static fn (): string => 'c2');

        self::assertSame($a, 'a1');
        self::assertSame($b, 'b2');
        self::assertSame($c, 'c1');
    }

    /**
     * @param callable(): mixed $factory
     */
    private static function viewMemoizedValue(LRUMemoizer $memoizer, string $key, callable $factory): mixed
    {
        return (clone $memoizer)->get($key, $factory);
    }
}

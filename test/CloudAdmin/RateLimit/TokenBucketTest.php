<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Test\CloudAdmin\RateLimit;

use CloudAdmin\RateLimit\TokenBucket;
use PHPUnit\Framework\TestCase;
use Swow\Coroutine;

/**
 * @internal
 * @coversNothing
 */
final class TokenBucketTest extends TestCase
{
    public function testBucket()
    {
        $bucket = new TokenBucket(100, 100);
        for ($i = 0; $i < 1000; ++$i) {
            Coroutine::run(function () use ($bucket) {
                $bucket->acquireToken();
                $this->assertTrue(true);
            });
        }
    }
}

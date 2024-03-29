<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Test\CloudAdmin\RedisLock;

use CloudAdmin\RedLock\Lock;
use CloudAdmin\RedLock\Options\LockOption;
use CloudAdmin\RedLock\Redis;
use CloudAdmin\Utils\Os;
use Hyperf\Engine\Coroutine;
use Hyperf\Redis\RedisFactory;
use PHPUnit\Framework\TestCase;
use Swow\Sync\WaitGroup;
use Throwable;

use function CloudAdmin\Utils\di;
use function sleep;
use function sprintf;
use function var_dump;

/**
 * @internal
 * @coversNothing
 */
final class LockTest extends TestCase
{
    public function testNonblockingLock()
    {
        $key = 'test_key';
        $this->assertSame('test_key', $key);
        $token = sprintf('%s_%s', Os::getProcessId(), Coroutine::id());
        $client = new Redis();
        $lockOption1 = (new LockOption())->withExpireSeconds(30);
        $lockOption1->repairLock();
        $lockOption2 = (new LockOption())->withBlock()->withBlockWaitingSeconds(2)->withExpireSeconds(20);
        $lockOption2->repairLock();
        $redisLock1 = new Lock($key, $token, $client, $lockOption1);
        $redisLock2 = new Lock($key, $token, $client, $lockOption2);
        $wg = new WaitGroup();
        $wg->add();
        Coroutine::create(function () use ($wg, $redisLock1) {
            \Swow\defer(function () use ($wg) {
                $wg->done();
            });
            try {
                $ret = $redisLock1->lock();
                if ($ret) {
                    echo "redisLock1---------Begin------------\n";
                    $this->assertTrue($ret);
                    sleep(1);
                    echo "redisLock1---------End------------\n";
                    $redisLock1->unlock();
                    echo "redisLock1---------Unlock-----------\n";
                }
            } catch (Throwable $exception) {
                echo 'redisLock1--------Exception------------' . Coroutine::id() . $exception->getMessage() . "\n";
            }
        });
        $wg->add();
        Coroutine::create(function () use ($wg, $redisLock2) {
            \Swow\defer(function () use ($wg) {
                $wg->done();
            });
            try {
                $ret = $redisLock2->lock();
                if ($ret) {
                    echo "redisLock2---------Begin------------\n";
                    $this->assertTrue($ret);
                    sleep(3);
                    echo "redisLock2---------End------------\n";
                    $redisLock2->unlock();
                    echo "redisLock2---------Unlock-----------\n";
                }
            } catch (Throwable $exception) {
                echo 'redisLock2--------Exception------------' . Coroutine::id() . $exception->getMessage() . "\n";
            }
        });
        $wg->wait();
    }

    public function testRedLock()
    {
        $this->assertTrue(true, 'ok');
    }

    public function testBlcokingLock()
    {
        $this->assertTrue(true, 'ok');
    }

    public function testRedisLock()
    {
        $redis1 = di()->get(RedisFactory::class)->get('default');
        $redis2 = di()->get(RedisFactory::class)->get('default');
        $key = '110';
        Coroutine::create(function () use ($redis1, $key) {
            $lock = new \CloudAdmin\RedisLock\Lock(di(), $redis1);
            $ret1 = $lock->lock($key);
            var_dump($ret1);
            echo PHP_EOL;
            sleep(2);
            $this->assertTrue($ret1, '加锁成功1');
        });
        Coroutine::create(function () use ($redis2, $key) {
            $lock = new \CloudAdmin\RedisLock\Lock(di(), $redis2);
            $ret2 = $lock->lock($key);
            var_dump($ret2);
            echo PHP_EOL;
            sleep(2);
            $this->assertTrue($ret2, '加锁成功2');
        });
        Coroutine::create(function () use ($redis2, $key) {
            $lock = new \CloudAdmin\RedisLock\Lock(di(), $redis2);
            $ret2 = $lock->lock($key);
            var_dump($ret2);
            echo PHP_EOL;
            sleep(2);
            $this->assertTrue($ret2, '加锁成功2');
        });
        sleep(5);
    }
}

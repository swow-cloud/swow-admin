<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\RedisLock;

use CloudAdmin\Interfaces\RedisLockInterface;
use CloudAdmin\Interfaces\WatchDogInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Swow\Coroutine;
use Throwable;

use function microtime;
use function sprintf;
use function usleep;

final class WatchDog implements WatchDogInterface
{
    /**
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sentinel(RedisLockInterface $lock, int $time = 60): bool
    {
        $logger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
        $cid = Coroutine::getCurrent()->getId();
        $logger->debug(
            sprintf('coroutine[%s] successfully initialize the watchdog task', $cid),
        );

        // Sleep time should be less than lock's ttl to prevent premature expiration
        $ttl = $lock->lockTtl();
        $sleepTime = ($ttl > 1 ? $ttl - 1 : 0.5) * 1000000; // microseconds
        $startTime = microtime(true);

        while (true) {
            try {
                // If lock is alive, attempt to keep it alive
                if ($lock->isAlive()) {
                    if (! $lock->keepAlive($ttl)) {
                        $logger->debug(
                            sprintf('coroutine[%s] cleanup watch dog when renewal failure', $cid),
                        );
                        break;
                    }

                    $logger->debug(
                        sprintf('coroutine[%s] watch dog successful renewal %s s', $cid, $ttl)
                    );
                } else {
                    // Lock is expired or not alive, cleanup watch dog
                    $logger->debug(
                        sprintf('coroutine[%s] cleanup watch dog  when the lock has expired', $cid),
                    );
                    break;
                }

                // If the total execution time of the watch dog exceeds the given limit,
                // break the loop and stop the watch dog
                if ((microtime(true) - $startTime) > $time) {
                    $logger->debug(
                        sprintf(
                            'coroutine[%s] cleanup watch dog; watch dog has exceeded the maximum watch time',
                            $cid,
                        ),
                    );
                    break;
                }

                // Sleep for a while before the next keep alive attempt
                usleep($sleepTime);
            } catch (Throwable) {
                // Log any exceptions and cleanup the watch dog
                $logger->debug(
                    sprintf('coroutine[%s] cleanup watch dog due to an exception', $cid),
                );
                break;
            }
        }

        return true;
    }
}

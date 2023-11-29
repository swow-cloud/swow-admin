<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\RateLimit;

use SplQueue;
use Swow\Channel;
use Swow\Coroutine;

use function microtime;
use function min;
use function sleep;

final class TokenBucket
{
    private int $capacity;

    private int $tokenRate;

    private int $tokens;

    private float $lastTime;

    private SplQueue $queue;

    private bool $tickerRunning;

    private Channel $channel;

    public function __construct(int $capacity, int $tokenRate)
    {
        $this->capacity = $capacity;
        $this->tokenRate = $tokenRate;
        $this->tokens = $capacity;
        $this->lastTime = microtime(true);
        $this->queue = new SplQueue();
        $this->tickerRunning = false;
        $this->channel = new Channel(0);
        $this->runTicker();
    }

    public function acquireToken(): void
    {
        if ($this->tokens < 1) {
            $this->runTicker();
            $coroutine = Coroutine::getCurrent();
            $this->queue->enqueue($coroutine);
            $coroutine->yield();
        } else {
            --$this->tokens;
        }
    }

    private function runTicker(): void
    {
        if (! $this->tickerRunning) {
            Coroutine::run(function () {
                while (true) {
                    if (! $this->channel->isAvailable()) {
                        break;
                    }
                    $this->addTokens();
                    sleep(1);
                }
            });
            $this->tickerRunning = true;
        }
    }

    private function setTokenRate(int $tokenRate): void
    {
        $this->tokenRate = $tokenRate;
    }

    private function addTokens(): void
    {
        $now = microtime(true);
        $tokens = ($now - $this->lastTime) * $this->tokenRate;
        $this->tokens = min($this->tokens + $tokens, $this->capacity);
        $this->lastTime = $now;

        if ($this->tokens >= 1 && ! $this->queue->isEmpty()) {
            $coroutine = $this->queue->dequeue();
            $coroutine->resume();
        } elseif ($this->tokens >= 1 && $this->queue->isEmpty()) {
            $this->tickerRunning = false;
        }
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace CloudAdmin\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessManager;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Swow\Coroutine;

class SwowServerStopHandler implements SignalHandlerInterface
{
    protected ContainerInterface $container;

    /**
     * @var ConfigInterface|mixed
     */
    protected ConfigInterface $config;

    /**
     * @var mixed|StdoutLoggerInterface
     */
    protected StdoutLoggerInterface $stdoutLogger;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->stdoutLogger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @return array[]
     */
    public function listen(): array
    {
        return [[self::WORKER, SIGTERM], [self::WORKER, SIGINT]];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);
        $this->stdoutLogger->error('Server shutdown');
        Coroutine::killAll();

        if (\Hyperf\Support\env('APP_DEBUG')) {
            foreach (Coroutine::getAll() as $coroutine) {
                if ($coroutine->isAlive()) {
                    $coroutine->kill();
                }
            }
        }
    }
}

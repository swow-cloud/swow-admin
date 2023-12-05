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

use function Hyperf\Support\env;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessManager;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Swow\Coroutine;

final class SwowServerStopHandler implements SignalHandlerInterface
{
    /**
     * @var ConfigInterface|mixed
     */
    private readonly ConfigInterface $config;

    /**
     * @var mixed|StdoutLoggerInterface
     */
    private readonly StdoutLoggerInterface $stdoutLogger;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(private readonly ContainerInterface $container)
    {
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
        $this->stdoutLogger->critical('Server shutdown');
        Coroutine::killAll();

        if (env('APP_DEBUG')) {
            foreach (Coroutine::getAll() as $coroutine) {
                if ($coroutine->isAlive()) {
                    $coroutine->kill();
                }
            }
        }
    }
}
